<?php

namespace App\Mail;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewerDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reviewAssignment;

    public $decision;

    public function __construct(ReviewAssignment $reviewAssignment, string $decision)
    {
        $this->reviewAssignment = $reviewAssignment;
        $this->decision = $decision;
    }

    public function build()
    {
        $articleTitle = $this->reviewAssignment->article->title ?? 'No Title';
        $reviewerName = $this->reviewAssignment->reviewer->name ?? 'Unknown Reviewer';

        $subject = $this->decision === 'accepted'
            ? 'قبول طلب المراجعة - Reviewer Assignment Accepted'
            : 'رفض طلب المراجعة - Reviewer Assignment Declined';

        return $this->subject($subject)
            ->view('emails.reviewer_decision')
            ->with([
                'reviewAssignment' => $this->reviewAssignment,
                'articleTitle' => $articleTitle,
                'reviewerName' => $reviewerName,
                'decision' => $this->decision,
            ]);
    }
}
