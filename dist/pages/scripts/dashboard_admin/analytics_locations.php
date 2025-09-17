<?php
// Fetch detailed evacuation records to display in the statistics area
$evacuationRecords = [];
$recQuery = "SELECT `evacuation_id`, `event_id`, `evacuation_location`, `start_date`, `end_date`, `total_solo`, `total_family`, `total_evacuation`, `total_infant`, `total_toddler`, `total_pre_school`, `total_school_age`, `total_teenage`, `total_adult`, `total_senior` FROM `evacuation_record_table` ORDER BY start_date DESC";
$recRes = mysqli_query($conn, $recQuery);
if ($recRes) {
    while ($r = mysqli_fetch_assoc($recRes)) {
        $evacuationRecords[] = $r;
    }
}
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Make dropdown searchable with Select2
    $('#evacuationCenterSelect').select2({
        placeholder: 'Select Evacuation Center',
        allowClear: true,
        width: '250px'
    });

    const evacuationData = <?= json_encode($analyticsByCenter) ?>;
    const ctx = document.getElementById('evacStatChart').getContext('2d');
    let evacChart;
    let evacRecordsChartInstance = null;
    // Detailed records from PHP
    const evacuationRecords = <?= json_encode($evacuationRecords, JSON_NUMERIC_CHECK) ?>;

    // Prepare a container under the chart to show the records table
    function ensureRecordsContainer() {
        let container = document.getElementById('evacRecordsContainer');
        if (!container) {
            const canvas = document.getElementById('evacStatChart');
            container = document.createElement('div');
            container.id = 'evacRecordsContainer';
            container.className = 'mt-3';
            canvas.parentNode.insertBefore(container, canvas.nextSibling);
        }
        return container;
    }

    function renderChart(centerName) {
        const centerData = evacuationData[centerName];
        if (!centerData) return;

        const labels = centerData.labels;
        const endDates = centerData.endDates;

        if (evacChart) evacChart.destroy();

        evacChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Completed (Family)', data: centerData.completedFamily, backgroundColor: '#3498db' },
                    { label: 'Ongoing (Family)', data: centerData.ongoingFamily, backgroundColor: '#85c1e9' },
                    { label: 'Completed (Solo)', data: centerData.completedSolo, backgroundColor: '#e74c3c' },
                    { label: 'Ongoing (Solo)', data: centerData.ongoingSolo, backgroundColor: '#f1948a' },
                    { label: 'Completed (Total Evacuees)', data: centerData.completedTotal, backgroundColor: '#f39c12' },
                    { label: 'Ongoing (Total Evacuees)', data: centerData.ongoingTotal, backgroundColor: '#f7dc6f' }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: {
                        display: true,
                        text: 'Evacuation Statistics (Family vs Solo)',
                        color: '#2c3e50',
                        font: { size: 18 }
                    },
                    tooltip: {
                        callbacks: {
                            title: (context) => {
                                const idx = context[0].dataIndex;
                                return `Start: ${labels[idx]}`;
                            },
                            afterTitle: (context) => {
                                const idx = context[0].dataIndex;
                                return `End: ${endDates[idx]}`;
                            },
                            label: (context) => `${context.dataset.label}: ${context.parsed.y ?? 0} evacuees`
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            align: 'center',
                            callback: function(value, index) {
                                const start = labels[index] || '';
                                const endRaw = endDates[index];
                                const end = (endRaw && endRaw !== '0000-00-00') ? endRaw : 'Ongoing';
                                return start + ' → ' + end;
                            }
                        },
                    categoryPercentage: 1.8,
                    barPercentage: 1.0,    
                    maxBarThickness: 30,
                    },
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Bind event so it works with Select2
    $('#evacuationCenterSelect').on('change', function() {
        const selected = $(this).val();
        if (!selected) {
            $('#evacuationLocationText').text('Showing data for all evacuation centers.');
            return;
        }
        $('#evacuationLocationText').text('Showing data for ' + selected + '.');
        renderChart(selected);
    });

    // Initial load
    const initialValue = $('#evacuationCenterSelect').val();
    if (initialValue) renderChart(initialValue);
    
    // Render records for a given centerName into the records container
    function renderRecords(centerName) {
        const container = ensureRecordsContainer();
        const rows = evacuationRecords.filter(r => (r.evacuation_location || '') === (centerName || ''));
        // Build aggregated metrics across the selected rows
        if (rows.length === 0) {
            container.innerHTML = '<div class="alert alert-secondary">No evacuation records found for this center.</div>';
            if (evacRecordsChartInstance) { try { evacRecordsChartInstance.destroy(); } catch (e) {} evacRecordsChartInstance = null; }
            return;
        }

        const metrics = {
            total_infant: 0,
            total_toddler: 0,
            total_pre_school: 0,
            total_school_age: 0,
            total_teenage: 0,
            total_adult: 0,
            total_senior: 0
        };

        rows.forEach(r => {
            metrics.total_infant += Number(r.total_infant) || 0;
            metrics.total_toddler += Number(r.total_toddler) || 0;
            metrics.total_pre_school += Number(r.total_pre_school) || 0;
            metrics.total_school_age += Number(r.total_school_age) || 0;
            metrics.total_teenage += Number(r.total_teenage) || 0;
            metrics.total_adult += Number(r.total_adult) || 0;
            metrics.total_senior += Number(r.total_senior) || 0;
        });

        // Build a grouped per-record chart showing Completed/Ongoing for Family and Solo
        // Helper: format the start_date into a readable text label
        function formatDateText(dateStr) {
            if (!dateStr) return '';
            try {
                // Normalize to date-only portion if datetime provided
                const ds = String(dateStr).split(' ')[0];
                const d = new Date(ds);
                if (isNaN(d)) return String(dateStr);
                return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            } catch (e) {
                return String(dateStr);
            }
        }

        // Sort rows so ongoing events appear on the right
        const sortedRows = rows.slice().sort((a, b) => {
            const aOngoing = !a.end_date || a.end_date === '0000-00-00';
            const bOngoing = !b.end_date || b.end_date === '0000-00-00';
            // aOngoing true should come after (right side) => return 1 when a ongoing and b not
            if (aOngoing === bOngoing) return 0;
            return aOngoing ? 1 : -1;
        });

        let labels = sortedRows.map(r => {
            const rawEnd = r.end_date;
            const isZeroEnd = !rawEnd || rawEnd === '0' || rawEnd === '0000-00-00' || rawEnd === '0000-00-00 00:00:00';
            const hasEnd = !isZeroEnd;
            if (!hasEnd) {
                // Ongoing event: show Ongoing with optional ID
                return 'Ongoing' + (r.event_id ? (' (ID:' + r.event_id + ')') : '');
            }
            const startText = formatDateText(r.start_date);
            const endText = formatDateText(r.end_date);
            const range = startText && endText ? (startText + ' → ' + endText) : (startText || endText || '');
            return range + (r.event_id ? (' (ID:' + r.event_id + ')') : '');
        });

        // For each row determine completed or ongoing based on end_date presence
        const completedFamily = [];
        const ongoingFamily = [];
        const completedSolo = [];
        const ongoingSolo = [];

        sortedRows.forEach(r => {
            const isCompleted = r.end_date && r.end_date !== '0000-00-00';
            const fam = Number(r.total_family) || 0;
            const solo = Number(r.total_solo) || 0;
            if (isCompleted) {
                completedFamily.push(fam);
                ongoingFamily.push(0);
                completedSolo.push(solo);
                ongoingSolo.push(0);
            } else {
                completedFamily.push(0);
                ongoingFamily.push(fam);
                completedSolo.push(0);
                ongoingSolo.push(solo);
            }
        });

        container.innerHTML = '<h6 class="mt-3">Evacuation Records for ' + (centerName || 'All') + '</h6><canvas id="evacRecordsGroupedBar" height="120"></canvas>';
        const groupedCanvas = container.querySelector('#evacRecordsGroupedBar');

        if (evacRecordsChartInstance) {
            try { evacRecordsChartInstance.destroy(); } catch (e) { console.warn(e); }
            evacRecordsChartInstance = null;
        }

        // Build per-record age-group datasets
        const infant = [];
        const toddler = [];
        const preSchool = [];
        const schoolAge = [];
        const teenage = [];
        const adult = [];
        const senior = [];

        sortedRows.forEach(r => {
            infant.push(Number(r.total_infant) || 0);
            toddler.push(Number(r.total_toddler) || 0);
            preSchool.push(Number(r.total_pre_school) || 0);
            schoolAge.push(Number(r.total_school_age) || 0);
            teenage.push(Number(r.total_teenage) || 0);
            adult.push(Number(r.total_adult) || 0);
            senior.push(Number(r.total_senior) || 0);
        });

        // To render records right-to-left, reverse labels and each dataset's data arrays
        labels = labels.slice().reverse();

        evacRecordsChartInstance = new Chart(groupedCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Infant', data: infant.slice().reverse(), backgroundColor: '#ff9ff3' },
                    { label: 'Toddler', data: toddler.slice().reverse(), backgroundColor: '#feca57' },
                    { label: 'Pre-school', data: preSchool.slice().reverse(), backgroundColor: '#ff6b6b' },
                    { label: 'School-age', data: schoolAge.slice().reverse(), backgroundColor: '#48dbfb' },
                    { label: 'Teenage', data: teenage.slice().reverse(), backgroundColor: '#0066ff' },
                    { label: 'Adult', data: adult.slice().reverse(), backgroundColor: '#10ac84' },
                    { label: 'Senior', data: senior.slice().reverse(), backgroundColor: '#576574' }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: true, text: 'Per-Record Age Group Breakdown' }
                },
                scales: {
                    x: { stacked: false },
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Update records when center selection changes
    $('#evacuationCenterSelect').on('change', function() {
        const selected = $(this).val();
        renderRecords(selected);
    });

    // Initial records render
    if (initialValue) renderRecords(initialValue);
});
</script>