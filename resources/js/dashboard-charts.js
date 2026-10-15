const palette = {
    primary: "#2563eb",
    primaryDark: "#1e3a8a",
    secondary: "#60a5fa",
    success: "#2e9e64",
    warning: "#d69e2e",
    danger: "#c53030",
    muted: "#556477",
    grid: "rgba(85, 100, 119, 0.12)",
};

const rupiah = (value) =>
    "Rp " + new Intl.NumberFormat("id-ID").format(Math.round(value));

const compactRupiah = (value) => {
    if (value >= 1_000_000_000) return "Rp " + value / 1_000_000_000 + " M";
    if (value >= 1_000_000) return "Rp " + value / 1_000_000 + " jt";
    if (value >= 1_000) return "Rp " + value / 1_000 + " rb";
    return "Rp " + value;
};

const applyDefaults = (Chart) => {
    Chart.defaults.font.family = "Inter, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = palette.muted;
    Chart.defaults.plugins.tooltip.backgroundColor = "#16202e";
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.displayColors = false;
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.boxWidth = 8;
    Chart.defaults.plugins.legend.labels.padding = 16;
};

const gradient = (ctx, color) => {
    const fill = ctx.createLinearGradient(0, 0, 0, 260);
    fill.addColorStop(0, color + "40");
    fill.addColorStop(1, color + "00");
    return fill;
};

const renderRevenue = (Chart, canvas, data) => {
    new Chart(canvas, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    type: "bar",
                    label: "Jumlah Transaksi",
                    data: data.transactions,
                    backgroundColor: palette.secondary + "59",
                    hoverBackgroundColor: palette.secondary,
                    borderRadius: 4,
                    barPercentage: 0.55,
                    categoryPercentage: 0.7,
                    yAxisID: "y1",
                    order: 2,
                },
                {
                    type: "line",
                    label: "Pendapatan",
                    data: data.revenue,
                    borderColor: palette.primary,
                    backgroundColor: (context) =>
                        gradient(context.chart.ctx, palette.primary),
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointBackgroundColor: palette.primary,
                    pointHoverBorderColor: "#ffffff",
                    pointHoverBorderWidth: 2,
                    yAxisID: "y",
                    order: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { position: "bottom" },
                tooltip: {
                    callbacks: {
                        label: (context) =>
                            context.dataset.yAxisID === "y"
                                ? " Pendapatan: " + rupiah(context.parsed.y)
                                : " Transaksi: " + context.parsed.y,
                    },
                },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    position: "left",
                    beginAtZero: true,
                    suggestedMax: 100_000_000,
                    border: { display: false },
                    grid: { color: palette.grid },
                    ticks: {
                        stepSize: 20_000_000,
                        callback: (value) => compactRupiah(value),
                    },
                },
                y1: {
                    position: "right",
                    beginAtZero: true,
                    suggestedMax: 5,
                    border: { display: false },
                    grid: { display: false },
                    ticks: { precision: 0, maxTicksLimit: 6 },
                },
            },
        },
    });
};

const renderStatus = (Chart, canvas, data) => {
    new Chart(canvas, {
        type: "doughnut",
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.values,
                    backgroundColor: [
                        palette.warning,
                        palette.secondary,
                        palette.primary,
                        palette.success,
                        palette.danger,
                    ],
                    borderWidth: 0,
                    hoverOffset: 8,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: "62%",
            plugins: {
                legend: { position: "bottom" },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const total = context.dataset.data.reduce(
                                (sum, value) => sum + value,
                                0,
                            );
                            const percent = total
                                ? Math.round((context.parsed / total) * 100)
                                : 0;
                            return ` ${context.label}: ${context.parsed} (${percent}%)`;
                        },
                    },
                },
            },
        },
    });
};

const renderStock = (Chart, canvas, data) => {
    new Chart(canvas, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: "Masuk",
                    data: data.in,
                    backgroundColor: palette.success,
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.6,
                },
                {
                    label: "Keluar",
                    data: data.out,
                    backgroundColor: palette.warning,
                    borderRadius: 4,
                    barPercentage: 0.7,
                    categoryPercentage: 0.6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { position: "bottom" },
                tooltip: {
                    callbacks: {
                        label: (context) =>
                            ` ${context.dataset.label}: ${context.parsed.y}`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: palette.grid },
                    ticks: { precision: 0 },
                },
            },
        },
    });
};

const renderTopWaste = (Chart, canvas, data) => {
    new Chart(canvas, {
        type: "bar",
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: "Qty",
                    data: data.values,
                    backgroundColor: palette.primary,
                    hoverBackgroundColor: palette.primaryDark,
                    borderRadius: 4,
                    barPercentage: 0.7,
                },
            ],
        },
        options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => " Qty: " + context.parsed.x,
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: palette.grid },
                    ticks: { precision: 0 },
                },
                y: { grid: { display: false } },
            },
        },
    });
};

const renderers = {
    "chart-revenue": renderRevenue,
    "chart-status": renderStatus,
    "chart-stock": renderStock,
    "chart-top-waste": renderTopWaste,
};

const initDashboardCharts = async () => {
    const payload = document.getElementById("dashboard-chart-data");

    if (!payload) {
        return;
    }

    const data = JSON.parse(payload.textContent);
    const { Chart, registerables } = await import("chart.js");

    Chart.register(...registerables);
    applyDefaults(Chart);

    Object.entries(renderers).forEach(([id, render]) => {
        const canvas = document.getElementById(id);

        if (canvas && data[id] && !Chart.getChart(canvas)) {
            render(Chart, canvas, data[id]);
        }
    });
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initDashboardCharts);
} else {
    initDashboardCharts();
}
