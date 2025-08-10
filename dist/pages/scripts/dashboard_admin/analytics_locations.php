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
                                return [labels[index], `(End: ${endDates[index]})`];
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
});
</script>