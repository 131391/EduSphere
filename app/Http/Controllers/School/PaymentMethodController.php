<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\TenantController;
use App\Http\Requests\School\StorePaymentMethodRequest;
use App\Http\Requests\School\UpdatePaymentMethodRequest;
use App\Models\PaymentMethod;
use App\Services\School\PaymentMethodService;
use Illuminate\Http\Request;

class PaymentMethodController extends TenantController
{
    protected PaymentMethodService $service;

    public function __construct(PaymentMethodService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'id'),
                'direction' => $request->input('direction', 'asc'),
            ];

            $methods = $this->service->getPaginatedMethods(
                $this->getSchool(),
                $this->validatePerPage(),
                $filters
            );

            return view('school.payment-methods.index', compact('methods'));
        } catch (\Exception $e) {
            return $this->backWithError('Failed to load payment methods.');
        }
    }

    public function store(StorePaymentMethodRequest $request)
    {
        try {
            $this->service->createMethod(
                $this->getSchool(),
                $request->validated()
            );

            return $this->redirectWithSuccess(
                'school.payment-methods.index',
                'Payment method created successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to create payment method: ' . $e->getMessage());
        }
    }

    public function update(UpdatePaymentMethodRequest $request, $id)
    {
        try {
            $method = PaymentMethod::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->updateMethod($method, $request->validated());

            return $this->redirectWithSuccess(
                'school.payment-methods.index',
                'Payment method updated successfully!'
            );
        } catch (\Exception $e) {
            return $this->backWithError('Failed to update payment method: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $method = PaymentMethod::where('school_id', $this->getSchoolId())
                ->findOrFail($id);

            $this->service->deleteMethod($method);

            return $this->redirectWithSuccess(
                'school.payment-methods.index',
                'Payment method deleted successfully!'
            );
        } catch (\Exception $e) {
            return $this->redirectWithError(
                'school.payment-methods.index',
                'Failed to delete payment method: ' . $e->getMessage()
            );
        }
    }
}
