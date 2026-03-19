/**
 * AI Article Generator Admin JS
 */

jQuery(document).ready(function ($) {
    const form = $('#aiag-generator-form');
    const resultsContainer = $('.aiag-generator-results');
    const formContainer = form.closest('.aiag-generator-form');

    // Form submission
    form.on('submit', function (e) {
        e.preventDefault();

        const prompt = $('#article-topic').val();
        const title = $('#article-title').val();
        const category = $('#article-category').val();

        if (!prompt.trim()) {
            alert('Please enter an article topic');
            return;
        }

        // Show results container
        formContainer.hide();
        resultsContainer.show();

        // Start generation process
        generateArticle(prompt, title, category);
    });

    function generateArticle(prompt, title, category) {
        updateProgress(10, 'Generating article content...');

        $.ajax({
            type: 'POST',
            url: aiagAjax.ajaxurl,
            data: {
                action: 'aiag_generate_article',
                prompt: prompt,
                title: title || '',
                category: category,
                _ajax_nonce: aiagAjax.nonce,
            },
            success: function (response) {
                if (response.success) {
                    const data = response.data;
                    displayResults(data);
                } else {
                    showError('Failed to generate article: ' + response.data);
                }
            },
            error: function () {
                showError('An error occurred while generating the article');
            },
        });
    }

    function displayResults(data) {
        updateProgress(100, 'Complete!');

        // Show preview
        $('#preview-title').html(escapeHtml(data.title));
        $('#preview-content').html(data.content);

        // Show quality results
        if (data.quality && data.quality.analysis) {
            displayQualityResults(data.quality.analysis);
        }

        // Show plagiarism results
        if (data.plagiarism && data.plagiarism.success) {
            displayPlagiarismResults(data.plagiarism);
        }

        // Show grammar results
        if (data.grammar && data.grammar.success) {
            displayGrammarResults(data.grammar);
        }

        // Show success message
        $('#success-message').html(
            '<strong>✓ Article generated successfully!</strong> Your draft has been created.'
        );

        // Update action buttons
        const editUrl = data.edit_link || '#';
        $('#edit-post-btn').attr('href', editUrl);
        $('#view-post-btn').attr('href', data.post_id ? '/wp-admin/post.php?post=' + data.post_id + '&action=edit' : '#');

        $('.aiag-results-content').show();
    }

    function displayQualityResults(analysis) {
        let html = '';

        // Overall score
        html += createScoreCard(
            'Overall Quality',
            analysis.overall_score,
            'Your article quality score'
        );

        // Readability
        if (analysis.readability) {
            html += createQualitySection('Readability', analysis.readability);
        }

        // SEO
        if (analysis.seo) {
            html += createQualitySection('SEO Analysis', analysis.seo);
        }

        // Engagement
        if (analysis.engagement) {
            html += createQualitySection('Engagement', analysis.engagement);
        }

        // Structure
        if (analysis.structure) {
            html += createQualitySection('Content Structure', analysis.structure);
        }

        $('#quality-results').html(html);
    }

    function createScoreCard(title, score, description) {
        const color = score >= 80 ? '#10b981' : score >= 60 ? '#f59e0b' : '#ef4444';
        return `
            <div class="aiag-quality-item">
                <h4>${title}</h4>
                <div class="aiag-quality-bar">
                    <div class="quality-bar-fill">
                        <div class="quality-bar-progress" style="width: ${score}%; background-color: ${color};"></div>
                    </div>
                    <div class="quality-score" style="color: ${color};">${score}/100</div>
                </div>
                <small>${description}</small>
            </div>
        `;
    }

    function createQualitySection(title, data) {
        let html = `<div class="aiag-quality-item"><h4>${title}</h4>`;

        if (data.score !== undefined) {
            html += createScoreCard('', data.score, '');
        }

        if (data.issues && data.issues.length > 0) {
            html += '<p><strong>Issues to address:</strong></p><ul class="quality-issues">';
            data.issues.forEach(function (issue) {
                html += `<li>${escapeHtml(issue)}</li>`;
            });
            html += '</ul>';
        }

        if (data.strengths && data.strengths.length > 0) {
            html += '<p><strong>✓ Strengths:</strong></p><ul class="quality-issues">';
            data.strengths.forEach(function (strength) {
                html += `<li>${escapeHtml(strength)}</li>`;
            });
            html += '</ul>';
        }

        html += '</div>';
        return html;
    }

    function displayPlagiarismResults(data) {
        let html = '<div class="aiag-quality-item">';
        html += `<h4>Plagiarism Score: ${data.percentage}%</h4>`;
        html += '<div class="aiag-quality-bar">';
        html += '<div class="quality-bar-fill">';

        const color =
            data.status === 'excellent' ? '#10b981' : data.status === 'good' ? '#3b82f6' : '#f59e0b';

        html += `<div class="quality-bar-progress" style="width: ${data.percentage}%; background-color: ${color};"></div>`;
        html += '</div>';
        html += `<div class="quality-score" style="color: ${color};">${data.percentage}%</div>`;
        html += '</div>';
        html += `<p><strong>${data.message}</strong></p>`;

        if (data.matches) {
            html += `<p>Found ${data.matches} potential matches</p>`;
        }

        html += '</div>';
        $('#plagiarism-results').html(html);
    }

    function displayGrammarResults(data) {
        let html = '<div class="aiag-quality-item">';
        html += `<h4>Grammar Score: ${data.score}/100</h4>`;

        html += '<div class="aiag-quality-bar">';
        html += '<div class="quality-bar-fill">';
        const color = data.status === 'excellent' ? '#10b981' : '#f59e0b';
        html += `<div class="quality-bar-progress" style="width: ${data.score}%; background-color: ${color};"></div>`;
        html += '</div>';
        html += `<div class="quality-score" style="color: ${color};">${data.score}</div>`;
        html += '</div>';

        // Issues
        if (data.issues && data.issues.length > 0) {
            html += '<p><strong>Grammar Issues:</strong></p><ul class="quality-issues">';
            data.issues.slice(0, 5).forEach(function (issue) {
                html += `<li><strong>${escapeHtml(issue.type)}:</strong> "${escapeHtml(issue.line)}" → ${escapeHtml(issue.suggestion)}</li>`;
            });
            if (data.issues.length > 5) {
                html += `<li><em>+ ${data.issues.length - 5} more issues</em></li>`;
            }
            html += '</ul>';
        }

        // Improvements
        if (data.improvements && data.improvements.length > 0) {
            html += '<p><strong>Improvements:</strong></p><ul class="quality-issues">';
            data.improvements.forEach(function (improvement) {
                html += `<li>${escapeHtml(improvement)}</li>`;
            });
            html += '</ul>';
        }

        html += '</div>';
        $('#grammar-results').html(html);
    }

    function updateProgress(percentage, text) {
        $('.progress-fill').css('width', percentage + '%');
        $('.progress-text').text(text);
    }

    function showError(message) {
        updateProgress(0, 'Error');
        const errorHtml = `<div class="aiag-alert alert-error"><strong>Error:</strong> ${escapeHtml(message)}</div>`;
        $('#success-message').html(errorHtml).show();
        $('.aiag-results-content').show();
    }

    // Reset button
    $('#generate-another-btn').on('click', function () {
        form.reset();
        formContainer.show();
        resultsContainer.hide();
        $('#preview-content').html('');
        $('#quality-results').html('');
        $('#plagiarism-results').html('');
        $('#grammar-results').html('');
    });

    // Escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        };
        return text.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    }
});
