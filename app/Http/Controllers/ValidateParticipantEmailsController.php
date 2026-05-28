<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EventParticipant;
use App\Services\EmailValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidateParticipantEmailsController extends Controller
{
    private const CHUNK_SIZE = 100;

    public function process(Request $request, string $eventId): JsonResponse
    {
        $participants = EventParticipant::where('eventId', $eventId)
            ->whereNull('invalid_reason')
            ->where('is_valid', false)
            ->select('id', 'email')
            ->limit(self::CHUNK_SIZE)
            ->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'done'      => true,
                'message'   => 'All emails have been validated for this event.',
                'remaining' => 0,
            ]);
        }

        $results = [
            'valid'   => 0,
            'invalid' => 0,
            'details' => [],
        ];

        foreach ($participants as $participant) {
            $email      = trim((string) $participant->email);
            $validation = EmailValidator::isDeliverable($email);

            if (!$validation['valid']) {
                $participant->update([
                    'is_valid'       => false,
                    'invalid_reason' => $validation['reason'],
                ]);
                $results['invalid']++;
            } else {
                $participant->update([
                    'is_valid'       => true,
                    'invalid_reason' => 'ok',
                ]);
                $results['valid']++;
            }

            $results['details'][] = [
                'email'  => $email,
                'valid'  => $validation['valid'],
                'reason' => $validation['reason'] ?? 'ok',
            ];
        }

        $remaining = EventParticipant::where('eventId', $eventId)
            ->whereNull('invalid_reason')
            ->where('is_valid', false)
            ->count();

        return response()->json([
            'done'      => $remaining === 0,
            'processed' => count($participants),
            'remaining' => $remaining,
            'results'   => $results,
        ]);
    }
}