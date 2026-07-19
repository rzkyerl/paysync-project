<?php

namespace Tests\Unit;

use App\Models\Payroll;
use PHPUnit\Framework\TestCase;

class PayrollStateMachineTest extends TestCase
{
    public function test_valid_payroll_transitions_are_allowed(): void
    {
        $payroll = new Payroll(['status' => 'draft']);
        $this->assertTrue($payroll->canTransitionTo('needs_review'));

        $payroll->status = 'needs_review';
        $this->assertTrue($payroll->canTransitionTo('pending_approval'));

        $payroll->status = 'pending_approval';
        $this->assertTrue($payroll->canTransitionTo('approved'));
        $this->assertTrue($payroll->canTransitionTo('needs_review'));

        $payroll->status = 'approved';
        $this->assertTrue($payroll->canTransitionTo('disbursed'));
    }

    public function test_invalid_transition_throws_logic_exception(): void
    {
        $payroll = new Payroll(['status' => 'draft']);

        $this->expectException(\LogicException::class);
        $payroll->transitionTo('approved');
    }
}
