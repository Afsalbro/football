<?php

namespace App\Http\Controllers;

use App\Events\ScoreUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class MatchController extends Controller
{
    private $matchData = [
        'team_a' => 'Manchester United',
        'team_b' => 'Liverpool',
        'score_a' => 0,
        'score_b' => 0,
        'status' => 'In Progress',
        'match_time' => '0'
    ];

    public function updateScore(Request $request)
    {
        try {
            $validated = $request->validate([
                'score_a' => 'nullable|integer|min:0',
                'score_b' => 'nullable|integer|min:0',
                'match_time' => 'nullable|string'
            ]);

            if (isset($validated['score_a'])) {
                $this->matchData['score_a'] = $validated['score_a'];
            }
            if (isset($validated['score_b'])) {
                $this->matchData['score_b'] = $validated['score_b'];
            }
            if (isset($validated['match_time'])) {
                $this->matchData['match_time'] = $validated['match_time'];
            }

            if ($this->matchData['score_a'] > $this->matchData['score_b']) {
                $this->matchData['status'] = 'Manchester United is leading';
            } elseif ($this->matchData['score_a'] < $this->matchData['score_b']) {
                $this->matchData['status'] = 'Liverpool is leading';
            } else {
                $this->matchData['status'] = 'Match is drawn';
            }

            if ($this->matchData['match_time'] == '90') {
                $this->matchData['status'] = 'Match is over';
            }

            broadcast(new ScoreUpdate($this->matchData));

            return response()->json([
                'success' => true,
                'data' => $this->matchData
            ]);
        } catch (Exception $e) {
            Log::error('Error updating score: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the score. Please try again later.',
            ], 500);
        }
    }

    public function getMatchData()
    {
        try {
            return response()->json($this->matchData);
        } catch (Exception $e) {
            Log::error('Error fetching match data: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the match data. Please try again later.',
            ], 500);
        }
    }
}
