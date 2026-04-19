<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StorePaymentMethodRequest;
use App\Http\Requests\School\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\School\PaymentMethodService;
use App\Traits\HasAjaxDataTable;
use Illuminate\Http\Request;

class PaymentMethodController extends TenantController
{
    use HasAjaxDataTable;

    protected PaymentMethodService $service;

    public function __construct(PaymentMethodService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $schoolId = $this->getSchoolId();

        $transformer = function ($method) {
            return [
                'id' => $method->id,
                'name' => $method->name,
                'code' => $method->code,
                'created_at' => $method->created_at?->format('M d, Y'),
                'created_at_human' => $method->created_at?->diffForHumans(),
            ];
        };

        $query = PaymentMethod::where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
        if (\in_array($sort, ['name', 'code', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $this->handleAjaxTable($query, $transformer, [
                'total' => PaymentMethod::where('school_id', $schoolId)->count(),
            ]);
        }

        $initialData = $this->getHydrationData($query, $transformer, [
            'stats' => [
                'total' => PaymentMethod::where('school_id', $schoolId)->count(),
            ],
        ]);

        return view('school.payment-methods.index', [
            'initialData' => $initialData,
            'stats' => $initialData['stats'],
        ]);
    }

    public function store(StorePaymentMethodRequest $request)
    {
        try {
            $method = $this->service->createMethod(
                $this->getSchool(),
                $request->validated()
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment method created successfully!',
                    'data' => $method
                ]);
            }

            return $this->redirectWithSuccess(
                'school.payment-methods.index',
                'Payment method created successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create payment method: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to create payment method: ' . $e->getMessage());
        }
    }

    public function update(UpdatePaymentMethodRequest $request, $id)
    {
        try {
            $method = PaymentMethod::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $method = $this->service->updateMethod($method, $request->validated());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment method updated successfully!',
                    'data' => $method
                ]);
            }

            return $this->redirectWithSuccess(
                'school.payment-methods.index',
                'Payment method updated successfully!'
            );
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment method: ' . $e->getMessage()
                ], 500);
            }
            return $this->backWithError('Failed to update payment method: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $method = PaymentMethod::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteMethod($method);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment method deleted successfully!'
                ]);
            }

            return $this->redirectWithSuccess(
                'school.payment-methods.index',
                'Payment method deleted successfully!'
            );
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete payment method: ' . $e->getMessage()
                ], 500);
            }
            return $this->redirectWithError(
                'school.payment-methods.index',
                'Failed to delete payment method: ' . $e->getMessage()
            );
        }
    }
}
