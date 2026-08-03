<?php

namespace Tests\Unit;

use App\Models\Payroll;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests untuk Payroll model — state machine canTransitionTo & transitionTo edge cases.
 * Melengkapi PayrollStateMachineTest yang sudah ada.
 */
class PayrollModelTest extends TestCase
{
    // -----------------------------------------------------------------------
    // canTransitionTo — edge cases yang belum di-cover
    // -----------------------------------------------------------------------

    public function test_disbursed_cannot_transition_to_any_status(): void
    {
        $payroll = new Payroll(['status' => 'disbursed']);

        $this->assertFalse($payroll->canTransitionTo('draft'));
        $this->assertFalse($payroll->canTransitionTo('needs_review'));
        $this->assertFalse($payroll->canTransitionTo('pending_approval'));
        $this->assertFalse($payroll->canTransitionTo('approved'));
        $this->assertFalse($payroll->canTransitionTo('disbursed'));
    }

    public function test_needs_review_cannot_skip_to_approved(): void
    {
        $payroll = new Payroll(['status' => 'needs_review']);

        $this->assertFalse($payroll->canTransitionTo('approved'));
        $this->assertFalse($payroll->canTransitionTo('disbursed'));
        $this->assertFalse($payroll->canTransitionTo('draft'));
    }

    public function test_draft_cannot_skip_to_pending_approval(): void
    {
        $payroll = new Payroll(['status' => 'draft']);

        $this->assertFalse($payroll->canTransitionTo('pending_approval'));
        $this->assertFalse($payroll->canTransitionTo('approved'));
        $this->assertFalse($payroll->canTransitionTo('disbursed'));
    }

    public function test_approved_cannot_go_back(): void
    {
        $payroll = new Payroll(['status' => 'approved']);

        $this->assertFalse($payroll->canTransitionTo('draft'));
        $this->assertFalse($payroll->canTransitionTo('needs_review'));
        $this->assertFalse($payroll->canTransitionTo('pending_approval'));
    }

    public function test_unknown_status_returns_false(): void
    {
        $payroll = new Payroll(['status' => 'unknown_status']);

        $this->assertFalse($payroll->canTransitionTo('approved'));
    }

    // -----------------------------------------------------------------------
    // transitionTo — metadata handling
    // -----------------------------------------------------------------------

    public function test_transition_to_approved_throws_logic_exception_from_draft(): void
    {
        $payroll = new Payroll(['status' => 'draft']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid payroll transition from draft to approved');
        $payroll->transitionTo('approved');
    }

    public function test_transition_to_disbursed_throws_from_needs_review(): void
    {
        $payroll = new Payroll(['status' => 'needs_review']);

        $this->expectException(\LogicException::class);
        $payroll->transitionTo('disbursed');
    }

    public function test_can_transition_pending_approval_back_to_needs_review(): void
    {
        $payroll = new Payroll(['status' => 'pending_approval']);

        $this->assertTrue($payroll->canTransitionTo('needs_review'));
    }
}
