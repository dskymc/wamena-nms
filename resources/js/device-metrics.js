import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Legend,
    Tooltip,
    Filler,
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Legend,
    Tooltip,
    Filler,
);

function formatBps(value) {
    if (value >= 1_000_000_000) {
        return `${(value / 1_000_000_000).toFixed(2)} Gbps`;
    }
    if (value >= 1_000_000) {
        return `${(value / 1_000_000).toFixed(2)} Mbps`;
    }
    if (value >= 1_000) {
        return `${(value / 1_000).toFixed(2)} Kbps`;
    }

    return `${value.toFixed(0)} bps`;
}

function hasChartData(values) {
    return Array.isArray(values) && values.length > 0;
}

function buildLineChart(canvasId, label, labels, values, color, ySuffix = '%') {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return null;
    }

    const chartValues = hasChartData(values) ? values : [0];
    const chartLabels = hasChartData(labels) ? labels : ['—'];

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label,
                data: chartValues,
                borderColor: color,
                backgroundColor: `${color}22`,
                fill: true,
                tension: 0.25,
                pointRadius: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => `${value}${ySuffix === '%' ? '%' : ''}`,
                    },
                },
            },
            plugins: {
                legend: { display: true },
            },
        },
    });
}

function buildTrafficChart(canvasId, labels, inValues, outValues) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        return null;
    }

    const chartLabels = hasChartData(labels) ? labels : ['—'];
    const chartIn = hasChartData(inValues) ? inValues : [0];
    const chartOut = hasChartData(outValues) ? outValues : [0];

    return new Chart(canvas, {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [
                {
                    label: 'Traffic In',
                    data: chartIn,
                    borderColor: '#2563eb',
                    backgroundColor: '#2563eb22',
                    fill: true,
                    tension: 0.25,
                    pointRadius: 2,
                },
                {
                    label: 'Traffic Out',
                    data: chartOut,
                    borderColor: '#16a34a',
                    backgroundColor: '#16a34a22',
                    fill: true,
                    tension: 0.25,
                    pointRadius: 2,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => formatBps(value),
                    },
                },
            },
        },
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('device-metrics-root');
    if (!root) {
        return;
    }

    const metricsUrl = root.dataset.metricsUrl;
    let range = root.dataset.range || '24h';
    let selectedInterface = root.dataset.defaultInterface || '';

    let cpuChart = null;
    let memoryChart = null;
    let trafficChart = null;

    const interfaceSelect = document.getElementById('metric-interface');
    const rangeButtons = document.querySelectorAll('[data-metric-range]');

    async function loadMetrics() {
        const params = new URLSearchParams({ range });
        if (selectedInterface) {
            params.set('interface', selectedInterface);
        }

        const response = await fetch(`${metricsUrl}?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (interfaceSelect) {
            const entries = Object.entries(data.interfaces || {});
            const current = selectedInterface;
            interfaceSelect.innerHTML = '';

            if (entries.length === 0) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Belum ada data interface';
                interfaceSelect.appendChild(option);
            } else {
                entries.forEach(([key, label]) => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = label;
                    option.selected = key === current;
                    interfaceSelect.appendChild(option);
                });

                if (!selectedInterface || !data.interfaces[selectedInterface]) {
                    selectedInterface = interfaceSelect.value;
                }
            }
        }

        if (cpuChart) cpuChart.destroy();
        if (memoryChart) memoryChart.destroy();
        if (trafficChart) trafficChart.destroy();

        cpuChart = buildLineChart('cpu-chart', 'CPU', data.cpu.labels, data.cpu.values, '#dc2626');
        memoryChart = buildLineChart('memory-chart', 'Memori', data.memory.labels, data.memory.values, '#9333ea');
        trafficChart = buildTrafficChart(
            'traffic-chart',
            data.traffic.labels,
            data.traffic.in,
            data.traffic.out,
        );
    }

    rangeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            range = button.dataset.metricRange;
            rangeButtons.forEach((b) => b.classList.remove('bg-indigo-600', 'text-white'));
            button.classList.add('bg-indigo-600', 'text-white');
            loadMetrics();
        });
    });

    if (interfaceSelect) {
        interfaceSelect.addEventListener('change', () => {
            selectedInterface = interfaceSelect.value;
            loadMetrics();
        });
    }

    loadMetrics();
    setInterval(loadMetrics, 60_000);
});
