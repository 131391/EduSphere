<?php

namespace Tests\Feature\PaymentGateways;

use App\Enums\FeeStatus;
use App\Enums\RelationType;
use App\Enums\StudentStatus;
use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\OnlineTransaction;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests for Parent\PaymentController@verify
 *
 * Covers:
 *  - Happy path: valid signature → payment recorded, transaction marked success
 *  - Signature mismatch: tampered HMAC → 422, no payment created
 *  - Ownership rejection: parent cannot verify a fee they don't own → 404
 */
class RazorpayVerifyTest extends TestCase
{
    use RefreshDatabase;

    private School $school;
    private AcademicYear $year;
    private Student $student;
    private Fee $fee;
    private User $parentUser;
    private StudentParent $parentRecord;
    private OnlineTransaction $transaction;
    private string $razorpaySecret = 'rzp_test_secret';

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass middleware that depends on subdomain resolution
        $this->withoutMiddleware([
            \App\Http\Middleware\TenantMiddleware::class,
            \App\Http\Middleware\SchoolAccessMiddleware::class,
        ]);

        config([
            'services.razorpay.key'            => 'rzp_test_key',
            'services.razorpay.secret'         => $this->razorpaySecret,
            'services.razorpay.webhook_secret' => 'whsec_test_secret',
            'services.razorpay.currency'       => 'INR',
        ]);

        $this->school = School::factory()->create();
        $this->year   = AcademicYear::factory()->create(['school_id' => $this->school->id]);

        $this->student = Student::factory()->create([
            'school_id'        => $this->school->id,
            'academic_year_id' => $this->year->id,
            'status'           => StudentStatus::Active,
        ]);

        $this->fee = Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'academic_year_id' => $this->year->id,
            'payable_amount'   => 10000,
            'paid_amount'      => 0,
            'due_amount'       => 10000,
            'payment_status'   => FeeStatus::Pending,
        ]);

        // Create a parent role
        $parentRole = Role::firstOrCreate(
            ['slug' => Role::PARENT],
            ['name' => 'Parent', 'guard_name' => 'web'],
        );

        // Create the parent user
        $this->parentUser = User::factory()->create([
            'school_id' => $this->school->id,
            'role_id'   => $parentRole->id,
        ]);

        // Create the StudentParent record
        $this->parentRecord = StudentParent::create([
            'school_id'  => $this->school->id,
            'user_id'    => $this->parentUser->id,
            'first_name' => 'Test',
            'last_name'  => 'Parent',
            'relation'   => RelationType::Father->value,
            'phone'      => '9876543210',
        ]);

        // Attach student to parent via pivot
        DB::table('student_parent')->insert([
            'student_id' => $this->student->id,
            'parent_id'  => $this->parentRecord->id,
            'relation'   => RelationType::Father->value,
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create the pending OnlineTransaction
        $this->transaction = OnlineTransaction::create([
            'school_id'        => $this->school->id,
            'student_id'       => $this->student->id,
            'fee_id'           => $this->fee->id,
            'amount'           => $this->fee->due_amount,
            'gateway_name'     => 'razorpay',
            'gateway_order_id' => 'order_test_happy',
            'status'           => OnlineTransaction::STATUS_PENDING,
        ]);

        app()->instance('currentSchool', $this->school);
    }

    /**
     * Helper: generate a valid Razorpay checkout signature.
     */
    private function sign(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId . '|' . $paymentId, $this->razorpaySecret);
    }

    // ─── Happy Path ────────────────────────────────────────────────

    public function test_verify_happy_path_records_payment_and_marks_success(): void
    {
        $this->actingAs($this->parentUser);

        $orderId   = $this->transaction->gateway_order_id;
        $paymentId = 'pay_test_happy_001';
        $signature = $this->sign($orderId, $paymentId);

        $response = $this->postJson(route('parent.payments.verify'), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id'   => $orderId,
            'razorpay_signature'  => $signature,
            'fee_id'              => $this->fee->id,
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true]);

        // FeePayment was created
        $this->assertDatabaseHas('fee_payments', [
            'fee_id'         => $this->fee->id,
            'transaction_id' => $paymentId,
            'school_id'      => $this->school->id,
        ]);

        // OnlineTransaction is now "success"
        $this->transaction->refresh();
        $this->assertEquals(OnlineTransaction::STATUS_SUCCESS, $this->transaction->status);
        $this->assertEquals($paymentId, $this->transaction->gateway_transaction_id);

        // Fee balance updated
        $this->fee->refresh();
        $this->assertEquals('10000.00', (string) $this->fee->paid_amount);
        $this->assertEquals('0.00', (string) $this->fee->due_amount);
        $this->assertEquals(FeeStatus::Paid, $this->fee->payment_status);
    }

    // ─── Signature Mismatch ────────────────────────────────────────

    public function test_verify_rejects_tampered_signature(): void
    {
        $this->actingAs($this->parentUser);

        $orderId   = $this->transaction->gateway_order_id;
        $paymentId = 'pay_test_tampered_001';
        // Deliberately wrong signature
        $badSig    = hash_hmac('sha256', 'TAMPERED_DATA', $this->razorpaySecret);

        $response = $this->postJson(route('parent.payments.verify'), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id'   => $orderId,
            'razorpay_signature'  => $badSig,
            'fee_id'              => $this->fee->id,
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false]);

        // No payment was created
        $this->assertDatabaseMissing('fee_payments', [
            'fee_id'         => $this->fee->id,
            'transaction_id' => $paymentId,
        ]);

        // Transaction stays pending
        $this->transaction->refresh();
        $this->assertEquals(OnlineTransaction::STATUS_PENDING, $this->transaction->status);

        // Fee balance unchanged
        $this->fee->refresh();
        $this->assertEquals('0.00', (string) $this->fee->paid_amount);
    }

    // ─── Ownership Rejection ──────────────────────────────────────

    public function test_verify_rejects_fee_not_owned_by_parent(): void
    {
        // Create a different student (not attached to our parent)
        $otherStudent = Student::factory()->create([
            'school_id'        => $this->school->id,
            'academic_year_id' => $this->year->id,
            'status'           => StudentStatus::Active,
        ]);

        $otherFee = Fee::factory()->create([
            'school_id'        => $this->school->id,
            'student_id'       => $otherStudent->id,
            'academic_year_id' => $this->year->id,
            'payable_amount'   => 5000,
            'paid_amount'      => 0,
            'due_amount'       => 5000,
            'payment_status'   => FeeStatus::Pending,
        ]);

        $otherTransaction = OnlineTransaction::create([
            'school_id'        => $this->school->id,
            'student_id'       => $otherStudent->id,
            'fee_id'           => $otherFee->id,
            'amount'           => 5000,
            'gateway_name'     => 'razorpay',
            'gateway_order_id' => 'order_test_other',
            'status'           => OnlineTransaction::STATUS_PENDING,
        ]);

        $this->actingAs($this->parentUser);

        $paymentId = 'pay_test_other_001';
        $signature = $this->sign($otherTransaction->gateway_order_id, $paymentId);

        // Parent tries to verify a fee that belongs to someone else's child
        $response = $this->postJson(route('parent.payments.verify'), [
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id'   => $otherTransaction->gateway_order_id,
            'razorpay_signature'  => $signature,
            'fee_id'              => $otherFee->id,
        ]);

        // resolveOwnedFee() should abort 404
        $response->assertStatus(404);

        // No payment created for the other fee
        $this->assertDatabaseMissing('fee_payments', [
            'fee_id' => $otherFee->id,
        ]);

        // Other fee balance unchanged
        $otherFee->refresh();
        $this->assertEquals('0.00', (string) $otherFee->paid_amount);
    }
}
