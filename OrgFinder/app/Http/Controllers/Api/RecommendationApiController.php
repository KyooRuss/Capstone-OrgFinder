<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class RecommendationApiController extends Controller
{
    // Weighted: interests = 3pts, skills = 2pts, activities = 1pt per match
    private const INTEREST_MAP = [
        'Technology'              => ['Technology', 'IT', 'Information Technology', 'Computer Science', 'Computing'],
        'Programming'             => ['Technology', 'IT', 'Programming', 'Computer Science', 'Software'],
        'Networking'              => ['Technology', 'IT', 'Networking', 'Network'],
        'Arts'                    => ['Arts', 'Creative', 'Fine Arts', 'Visual Arts'],
        'Gaming'                  => ['Gaming', 'E-sports', 'Esports', 'Game'],
        'Design'                  => ['Design', 'Creative', 'Arts', 'UI/UX', 'Graphic'],
        'Animation'               => ['Arts', 'Design', 'Creative', 'Animation', 'Multimedia'],
        'Cyber Security'          => ['Technology', 'Cybersecurity', 'Cyber Security', 'IT', 'Security'],
        'Artificial Intelligence' => ['Technology', 'AI', 'Machine Learning', 'Data Science', 'Computer Science'],
        'Analytics'               => ['Technology', 'Data Science', 'Analytics', 'IT'],
        'Machine Learning'        => ['Technology', 'AI', 'Machine Learning', 'Data Science'],
        'Innovation'              => ['Technology', 'Entrepreneurship', 'Innovation', 'Business'],
    ];

    private const SKILL_MAP = [
        'Public Speaking'    => ['Leadership', 'Communication', 'Education', 'Management'],
        'Leadership'         => ['Leadership', 'Management', 'Organization'],
        'Project Management' => ['Leadership', 'Management', 'Business', 'Organization'],
        'Arts'               => ['Arts', 'Creative', 'Fine Arts', 'Visual Arts'],
        'Programming'        => ['Technology', 'IT', 'Computer Science', 'Programming'],
        'Cybersecurity'      => ['Technology', 'Cybersecurity', 'Cyber Security', 'IT'],
        'UI/UX Design'       => ['Design', 'Technology', 'Creative', 'Arts'],
        'Graphic Design'     => ['Design', 'Arts', 'Creative', 'Multimedia'],
    ];

    private const ACTIVITY_MAP = [
        'Training'    => ['Training', 'Education', 'Leadership', 'Management'],
        'Forum'       => ['Communication', 'Leadership', 'Education', 'Organization'],
        'Seminar'     => ['Education', 'Leadership', 'Academic', 'Organization'],
        'Competition' => ['Competition', 'E-sports', 'Gaming', 'Sports', 'Academic'],
        'E-sports'    => ['E-sports', 'Gaming', 'Esports', 'Competition'],
        'Workshop'    => ['Education', 'Training', 'Creative', 'Technology', 'Arts'],
    ];

    // Program → likely categories for bonus matching
    private const PROGRAM_MAP = [
        'BSIT'  => ['Technology', 'IT', 'Information Technology', 'Computer Science', 'Programming', 'Networking', 'Cybersecurity'],
        'BSCS'  => ['Computer Science', 'Technology', 'Programming', 'AI', 'Machine Learning', 'Software'],
        'BSIS'  => ['Technology', 'IT', 'Information Systems', 'Business', 'Management'],
        'BSCpE' => ['Technology', 'Computer Science', 'Engineering', 'Hardware', 'Networking'],
    ];

    public function index(Request $request)
    {
        $user = $request->user();

        $orgs = Organization::with(['photos'])
            ->whereNull('deleted_at')
            ->get()
            ->filter(function ($org) use ($user) {
                $eligible = $org->eligible_programs;
                return empty($eligible) || in_array($user->program, $eligible);
            });

        $scored = $orgs->map(function ($org) use ($user) {
            [$score, $matchedTags] = $this->scoreOrg($org, $user);
            return [
                'org'         => $org,
                'score'       => $score,
                'matchedTags' => $matchedTags,
            ];
        })
        ->sortByDesc('score')
        ->values();

        $matched   = $scored->filter(fn($i) => $i['score'] > 0)->take(10)->values();
        $displayed = $matched->isNotEmpty() ? $matched : $scored->take(10)->values();

        return response()->json([
            'recommendations' => $displayed->map(fn($item) => $this->formatOrg($item)),
        ]);
    }

    private function scoreOrg(Organization $org, $user): array
    {
        $score       = 0;
        $matchedTags = [];
        $category    = strtolower($org->category ?? '');

        if (!$category) return [0, []];

        // Interests → 3 pts each
        foreach (($user->interests ?? []) as $interest) {
            $cats = array_map('strtolower', self::INTEREST_MAP[$interest] ?? []);
            if (in_array($category, $cats) || $this->partialMatch($category, $cats)) {
                $score += 3;
                $matchedTags[] = $interest;
            }
        }

        // Skills → 2 pts each
        foreach (($user->skills ?? []) as $skill) {
            $cats = array_map('strtolower', self::SKILL_MAP[$skill] ?? []);
            if (in_array($category, $cats) || $this->partialMatch($category, $cats)) {
                $score += 2;
                $matchedTags[] = $skill;
            }
        }

        // Activities → 1 pt each
        foreach (($user->activities ?? []) as $activity) {
            $cats = array_map('strtolower', self::ACTIVITY_MAP[$activity] ?? []);
            if (in_array($category, $cats) || $this->partialMatch($category, $cats)) {
                $score += 1;
                $matchedTags[] = $activity;
            }
        }

        // Program bonus → +1 pt if category aligns with program
        $programCats = array_map('strtolower', self::PROGRAM_MAP[$user->program ?? ''] ?? []);
        if (!empty($programCats) && (in_array($category, $programCats) || $this->partialMatch($category, $programCats))) {
            $score += 1;
        }

        return [$score, array_unique($matchedTags)];
    }

    private function partialMatch(string $category, array $cats): bool
    {
        foreach ($cats as $c) {
            if (str_contains($category, $c) || str_contains($c, $category)) {
                return true;
            }
        }
        return false;
    }

    private function formatOrg(array $item): array
    {
        $org  = $item['org'];
        $tags = array_values($item['matchedTags']);
        $score = $item['score'];

        // Build match reason from tags
        if (!empty($tags)) {
            $reason = 'Matches your ' . implode(', ', array_slice($tags, 0, 3));
        } else {
            $reason = 'Explore this organization';
        }

        // Match percentage capped at 100 (max possible = 3*3 + 3*2 + 3*1 + 1 = 16)
        $matchPct = min(100, (int) round(($score / 16) * 100));

        return [
            'id'           => $org->id,
            'name'         => $org->name,
            'category'     => $org->category,
            'president'    => $org->president,
            'mission'      => $org->mission,
            'logo'         => $org->logo ? asset('storage/' . $org->logo) : null,
            'score'        => $score,
            'match_pct'    => $matchPct,
            'match_reason' => $reason,
            'match_tags'   => $tags,
        ];
    }
}
