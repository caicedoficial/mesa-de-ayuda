/**
 * Lazy Loading for Ticket History
 * PERFORMANCE FIX: Only loads history when user scrolls to history section
 */

(function() {
    'use strict';

    /**
     * Load ticket history via AJAX
     * @param {number} ticketId - Ticket ID
     */
    function loadTicketHistory(ticketId) {
        const container = document.getElementById('history-container');
        const loader = document.getElementById('history-loader');
        const content = document.getElementById('history-content');

        // Check if already loaded
        if (container.dataset.loaded === 'true') {
            return;
        }

        // Mark as loaded to prevent duplicate requests
        container.dataset.loaded = 'true';

        // Fetch history from AJAX endpoint
        fetch(`/tickets/history/${ticketId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to load history');
            }
            return response.json();
        })
        .then(data => {
            // Hide loader
            loader.style.display = 'none';

            // Extract history array from response (CakePHP wraps it in an object)
            const history = data.history || data;

            // Render history
            if (!history || history.length === 0) {
                content.innerHTML = '<p class="text-muted small">No hay historial de cambios para este ticket.</p>';
            } else {
                content.innerHTML = renderHistory(history);
            }

            // Show content
            content.style.display = 'block';
        })
        .catch(() => {
            loader.innerHTML = '<p class="text-danger small">Error al cargar el historial.</p>';
        });
    }

    /**
     * Render history HTML from JSON data
     * @param {Array} history - History entries
     * @returns {string} HTML string
     */
    function renderHistory(history) {
        let html = '<div class="timeline">';

        history.forEach(entry => {
            // Icon based on field changed
            let icon = 'circle-fill';
            let iconColor = 'text-secondary';

            if (entry.field_name === 'status') {
                icon = 'arrow-repeat';
                iconColor = 'text-primary';
            } else if (entry.field_name === 'assignee_id') {
                icon = 'person-fill';
                iconColor = 'text-success';
            } else if (entry.field_name === 'priority') {
                icon = 'exclamation-triangle-fill';
                iconColor = 'text-warning';
            }

            html += `
                <div class="timeline-item mb-3">
                    <div class="d-flex gap-2">
                        <div class="timeline-icon flex-shrink-0 position-relative">
                            <i class="bi bi-${icon} ${iconColor}" style="font-size: 12px; position: absolute; top: 0;"></i>
                        </div>
                        <div class="flex-grow-1" style="margin-left: 18px;">
                            <div class="small mb-1">
                                <strong>${escapeHtml(entry.user.name)}</strong>
                            </div>
            `;

            if (entry.description) {
                html += `<div class="small text-muted mb-1">${escapeHtml(entry.description)}</div>`;
            } else {
                const fieldName = entry.field_name.replace(/_/g, ' ');
                html += `<div class="small text-muted mb-1">`;
                html += `<strong>${escapeHtml(fieldName.charAt(0).toUpperCase() + fieldName.slice(1))}:</strong> `;

                if (entry.old_value) {
                    html += `<span class="text-decoration-line-through">${escapeHtml(entry.old_value)}</span> → `;
                }

                html += `<span>${escapeHtml(entry.new_value)}</span>`;
                html += `</div>`;
            }

            // Format timestamp
            const date = new Date(entry.created);
            const formattedDate = formatRelativeTime(date);

            html += `
                            <div class="small text-muted fw-bold">${formattedDate}</div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    /**
     * Escape HTML to prevent XSS
     * @param {string} text - Text to escape
     * @returns {string} Escaped text
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Format date as relative time (e.g., "hace 2 horas")
     * @param {Date} date - Date to format
     * @returns {string} Formatted date
     */
    function formatRelativeTime(date) {
        const now = new Date();
        const diffMs = now - date;
        const diffSec = Math.floor(diffMs / 1000);
        const diffMin = Math.floor(diffSec / 60);
        const diffHour = Math.floor(diffMin / 60);
        const diffDay = Math.floor(diffHour / 24);

        if (diffSec < 60) {
            return 'hace unos segundos';
        } else if (diffMin < 60) {
            return `hace ${diffMin} minuto${diffMin !== 1 ? 's' : ''}`;
        } else if (diffHour < 24) {
            return `hace ${diffHour} hora${diffHour !== 1 ? 's' : ''}`;
        } else if (diffDay < 7) {
            return `hace ${diffDay} día${diffDay !== 1 ? 's' : ''}`;
        } else {
            // Format as date for older entries
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }

    /**
     * Initialize lazy loading with Intersection Observer
     */
    function initLazyLoading() {
        const container = document.getElementById('history-container');

        if (!container) {
            return; // Not on ticket view page
        }

        const ticketId = container.dataset.ticketId;

        // Use Intersection Observer for efficient lazy loading
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && container.dataset.loaded === 'false') {
                    loadTicketHistory(ticketId);
                    observer.unobserve(container); // Stop observing once loaded
                }
            });
        }, {
            root: null, // viewport
            rootMargin: '50px', // Load 50px before it becomes visible
            threshold: 0.1 // Trigger when 10% visible
        });

        observer.observe(container);
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLazyLoading);
    } else {
        initLazyLoading();
    }
})();
