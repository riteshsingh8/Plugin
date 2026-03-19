<?php
/**
 * Content Quality Analyzer Class
 */

class AIAG_Content_Analyzer {

    /**
     * Analyze content quality and SEO
     */
    public function analyze($content) {
        $analysis = array(
            'readability' => $this->analyze_readability($content),
            'seo' => $this->analyze_seo($content),
            'engagement' => $this->analyze_engagement($content),
            'structure' => $this->analyze_structure($content),
            'overall_score' => 0
        );

        // Calculate overall score
        $analysis['overall_score'] = round(
            ($analysis['readability']['score'] +
             $analysis['seo']['score'] +
             $analysis['engagement']['score'] +
             $analysis['structure']['score']) / 4
        );

        return array(
            'success' => true,
            'analysis' => $analysis
        );
    }

    /**
     * Analyze readability metrics
     */
    private function analyze_readability($content) {
        $text = wp_strip_all_tags($content);
        $sentences = preg_split('/[.!?]+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $words = str_word_count($text);
        $paragraphs = substr_count($content, '</p>');

        $avg_sentence_length = $words / max(count($sentences), 1);
        $avg_paragraph_length = $words / max($paragraphs, 1);

        $score = 100;
        $issues = array();

        // Check sentence length (ideal: 15-20 words)
        if ($avg_sentence_length > 25) {
            $score -= 10;
            $issues[] = 'Sentences are too long. Keep them under 20 words.';
        }

        // Check paragraph length (ideal: 50-150 words)
        if ($avg_paragraph_length > 200) {
            $score -= 10;
            $issues[] = 'Paragraphs are too long. Break them into smaller chunks.';
        }

        // Check for short paragraphs (good for readability)
        if ($avg_paragraph_length < 30) {
            $score -= 5;
            $issues[] = 'Some paragraphs might be too short.';
        }

        return array(
            'score' => $score,
            'avg_sentence_length' => round($avg_sentence_length, 1),
            'avg_paragraph_length' => round($avg_paragraph_length, 1),
            'total_words' => $words,
            'issues' => $issues,
            'status' => $score >= 80 ? 'excellent' : 'good'
        );
    }

    /**
     * Analyze SEO elements
     */
    private function analyze_seo($content) {
        $text = wp_strip_all_tags($content);
        $words = str_word_count($text);

        $score = 100;
        $issues = array();
        $recommendations = array();

        // Check content length (minimum 1000 words recommended)
        if ($words < 1000) {
            $score -= 20;
            $issues[] = 'Content is too short. Aim for at least 1000 words.';
        }

        if ($words > 3000 && $words < 5000) {
            $recommendations[] = 'Consider expanding to 5000+ words for better SEO.';
        }

        // Check for headings
        $h2_count = substr_count($content, '<h2');
        $h3_count = substr_count($content, '<h3');

        if ($h2_count === 0) {
            $score -= 15;
            $issues[] = 'No H2 headings found. Add section headings.';
        }

        if ($h2_count > 0 && $h3_count === 0) {
            $recommendations[] = 'Add H3 subheadings for better structure.';
        }

        // Check for meta description length
        $meta_desc_needed = 160;
        $recommendations[] = "Add meta description (120-160 characters).";

        return array(
            'score' => max($score, 0),
            'word_count' => $words,
            'h2_headings' => $h2_count,
            'h3_headings' => $h3_count,
            'issues' => $issues,
            'recommendations' => $recommendations,
            'status' => $score >= 75 ? 'good' : 'needs_work'
        );
    }

    /**
     * Analyze engagement factors
     */
    private function analyze_engagement($content) {
        $text = wp_strip_all_tags($content);
        $score = 100;
        $issues = array();
        $strengths = array();

        // Check for lists
        $list_count = substr_count($content, '<li') + substr_count($content, '- ');
        if ($list_count > 2) {
            $strengths[] = 'Good use of lists for scannability';
        } else {
            $score -= 10;
            $issues[] = 'Add bullet lists to improve readability';
        }

        // Check for transitions/connectors
        $transition_words = array('however', 'furthermore', 'moreover', 'in addition', 'meanwhile');
        $transition_count = 0;
        foreach ($transition_words as $word) {
            $transition_count += substr_count(strtolower($text), $word);
        }

        if ($transition_count < 3) {
            $score -= 5;
            $issues[] = 'Use more transition words between paragraphs';
        } else {
            $strengths[] = 'Good flow with transition words';
        }

        // Check for questions (engagement element)
        $questions = substr_count($text, '?');
        if ($questions > 2) {
            $strengths[] = 'Good use of questions to engage readers';
        }

        // Check for action verbs
        $action_verbs = array('discover', 'explore', 'understand', 'learn', 'create', 'build', 'improve');
        $action_count = 0;
        foreach ($action_verbs as $verb) {
            $action_count += substr_count(strtolower($text), $verb);
        }

        if ($action_count > 3) {
            $strengths[] = 'Good use of action verbs';
        }

        return array(
            'score' => max($score, 0),
            'lists_count' => $list_count,
            'questions_count' => $questions,
            'action_verbs_count' => $action_count,
            'issues' => $issues,
            'strengths' => $strengths,
            'status' => $score >= 80 ? 'excellent' : 'good'
        );
    }

    /**
     * Analyze content structure
     */
    private function analyze_structure($content) {
        $score = 100;
        $issues = array();
        $strengths = array();

        // Check for introduction
        $intro_words = substr_count(strtolower($content), 'introduction') +
                      substr_count(strtolower($content), 'welcome') +
                      substr_count(strtolower($content), 'overview');

        if ($intro_words === 0) {
            $issues[] = 'Consider adding an introduction';
            $score -= 10;
        } else {
            $strengths[] = 'Has clear introduction';
        }

        // Check for conclusion
        $conclusion_words = substr_count(strtolower($content), 'conclusion') +
                           substr_count(strtolower($content), 'summary') +
                           substr_count(strtolower($content), 'wrap');

        if ($conclusion_words === 0) {
            $issues[] = 'Add a conclusion or summary section';
            $score -= 10;
        } else {
            $strengths[] = 'Has clear conclusion';
        }

        // Check for call-to-action
        $cta_words = substr_count(strtolower($content), 'learn more') +
                    substr_count(strtolower($content), 'contact us') +
                    substr_count(strtolower($content), 'subscribe') +
                    substr_count(strtolower($content), 'get started');

        if ($cta_words === 0) {
            $issues[] = 'Add a call-to-action';
            $score -= 5;
        } else {
            $strengths[] = 'Includes call-to-action';
        }

        return array(
            'score' => max($score, 0),
            'has_intro' => $intro_words > 0,
            'has_conclusion' => $conclusion_words > 0,
            'has_cta' => $cta_words > 0,
            'issues' => $issues,
            'strengths' => $strengths,
            'status' => $score >= 80 ? 'excellent' : 'good'
        );
    }
}
