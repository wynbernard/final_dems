<!-- Modal -->
<div class="modal fade" id="idpDetailsModal" tabindex="-1" aria-labelledby="idpDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
            <div class="modal-header details-modal-header w-100">
                <div class="w-100 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="modal-title d-flex align-items-center gap-2 m-0" id="idpDetailsModalLabel"><i class="fas fa-users text-primary"></i> <span id="detailsTitle">Details</span></h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap" id="detailsChips"></div>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body details-modal-body" id="idpDetailsBody">
				<!-- IDP details will be loaded here dynamically -->
				Loading...
			</div>
            <div class="modal-footer details-modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i> Close</button>
			</div>
		</div>
	</div>
</div>

<style>
    /* Scoped styles for Details Modal */
    #idpDetailsModal .details-modal-header {
        background: linear-gradient(90deg, rgba(13,110,253,0.10) 0%, rgba(13,110,253,0.02) 100%);
        border-bottom: 1px solid #e9ecef;
    }
    #idpDetailsModal .details-modal-body {
        background-color: #f8f9fb;
    }
    #idpDetailsModal .details-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: .75rem;
        box-shadow: 0 10px 24px rgba(0,0,0,.05);
        padding: 1rem;
    }
    #idpDetailsModal .details-card + .details-card { margin-top: .75rem; }
    #idpDetailsModal .table {
        --bs-table-bg: transparent;
        margin-bottom: 0;
    }
    #idpDetailsModal .table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f1f3f5;
        border-bottom: 1px solid #dee2e6;
    }
    #idpDetailsModal h6 {
        letter-spacing: .2px;
        text-transform: uppercase;
        font-size: .9rem;
        color: #334155;
    }
    #idpDetailsModal .badge {
        font-weight: 600;
    }
    #idpDetailsModal .table tbody tr:nth-child(even) { background-color: #fafbfc; }
    #idpDetailsModal .table tbody tr:hover {
        background: #f8f9fa;
    }
    #idpDetailsModal .badge-soft-primary {
        background: rgba(13,110,253,.12);
        color: #0d6efd;
        border: 1px solid rgba(13,110,253,.24);
    }
    #idpDetailsModal .chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .5rem;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        font-size: .8rem;
        color: #334155;
    }
    #idpDetailsModal .chip i { color: #64748b; }
    #idpDetailsModal .details-modal-footer {
        background: #fff;
        border-top: 1px solid #e9ecef;
    }
    @media (max-width: 576px) {
        #idpDetailsModal .modal-dialog { margin: .5rem; }
        #idpDetailsModal .details-card { padding: .75rem; }
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Handle click event on the "View Details" button
        $('.view-idp-btn').on('click', function() {
            const evacRegId = $(this).data('id');
            const name = $(this).data('name') || '';
            const hasFamily = String($(this).data('has-family')) === '1';
            const location = $(this).data('location') || '';
            const room = $(this).data('room') || '';
            const date = $(this).data('date') || '';
            const registered = ($(this).data('registered') || '').toString();
            // Read pre-rendered family content from the corresponding hidden div
            const content = document.getElementById('family-members-' + evacRegId);
            if (content) {
                // Wrap inside a styled card for consistent appearance
                $('#idpDetailsBody').html('<div class="details-card">' + content.innerHTML + '</div>');
            } else {
                $('#idpDetailsBody').html('<div class="p-3">No details available.</div>');
            }

            // Set friendly, contextual title
            $('#detailsTitle').text(hasFamily ? 'Family Details' : 'Individual Details');
            // Build chips row
            const chips = [];
            if (name) chips.push('<span class="chip"><i class="fas fa-user"></i>' + name + '</span>');
            if (registered) chips.push('<span class="chip"><i class="fas fa-id-badge"></i>' + registered + '</span>');
            if (location) chips.push('<span class="chip"><i class="fas fa-map-marker-alt"></i>' + location + '</span>');
            if (room) chips.push('<span class="chip"><i class="fas fa-door-open"></i>' + room + '</span>');
            if (date) chips.push('<span class="chip"><i class="fas fa-calendar"></i>' + date + '</span>');
            $('#detailsChips').html(chips.join(''));
        });
    });
    </script>