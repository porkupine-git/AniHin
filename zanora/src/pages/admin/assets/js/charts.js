/* ==========================================================================
   ZANORA ADMIN OS - MONOCHROME CHARTS & VECTOR MAP CONTROLLER
   ========================================================================== */

let dailyChartObj = null;
let trafficChartObj = null;
let candlestickChartObj = null;
let worldMapObj = null;

/* --------------------------------------------------------------------------
   1. DAILY TIMELINE LINE CHART (Chart.js with Fallback)
   -------------------------------------------------------------------------- */
function renderDailyChart(canvasId, statsData, retryCount = 0) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !statsData) return;

    if (!window.Chart) {
        if (retryCount < 10) {
            setTimeout(() => renderDailyChart(canvasId, statsData, retryCount + 1), 150);
        }
        return;
    }

    if (dailyChartObj) {
        try { dailyChartObj.destroy(); } catch(e) {}
    }

    try {
        const ctx = canvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.28)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0.01)');

        dailyChartObj = new Chart(ctx, {
            type: 'line',
            data: {
                labels: statsData.days || [],
                datasets: [
                    {
                        label: 'Page Views',
                        data: statsData.pageviews || [],
                        borderColor: '#6366f1',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointBackgroundColor: '#818cf8'
                    },
                    {
                        label: 'Unique Visitors',
                        data: statsData.unique || [],
                        borderColor: '#0ea5e9',
                        backgroundColor: 'transparent',
                        fill: false,
                        tension: 0.35,
                        borderWidth: 2,
                        borderDash: [4, 4],
                        pointRadius: 2.5,
                        pointBackgroundColor: '#38bdf8'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: '#94a3b8', font: { family: 'Inter', size: 12 } }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#64748b', font: { family: 'Inter', size: 11 } }
                    }
                }
            }
        });
    } catch(err) {
        console.warn('[Zanora Charts] Daily chart render warning:', err);
    }
}

/* --------------------------------------------------------------------------
   2. TRAFFIC SOURCES DOUGHNUT CHART
   -------------------------------------------------------------------------- */
function renderTrafficChart(canvasId, trafficData, retryCount = 0) {
    const canvas = document.getElementById(canvasId);
    if (!canvas || !trafficData) return;

    if (!window.Chart) {
        if (retryCount < 10) {
            setTimeout(() => renderTrafficChart(canvasId, trafficData, retryCount + 1), 150);
        }
        return;
    }

    if (trafficChartObj) {
        try { trafficChartObj.destroy(); } catch(e) {}
    }

    try {
        trafficChartObj = new Chart(canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Direct', 'Search Engine', 'Social Media', 'Referrals'],
                datasets: [{
                    data: [
                        trafficData.direct || 0,
                        trafficData.search || 0,
                        trafficData.social || 0,
                        trafficData.referral || 0
                    ],
                    backgroundColor: ['#6366f1', '#0ea5e9', '#a855f7', '#10b981'],
                    borderColor: '#131e33',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 }, padding: 14 }
                    }
                }
            }
        });
    } catch(err) {
        console.warn('[Zanora Charts] Traffic chart render warning:', err);
    }
}

/* --------------------------------------------------------------------------
   3. CANDLESTICK DAILY ACTIVE USERS FLOW (ApexCharts + Native SVG Fallback)
   -------------------------------------------------------------------------- */
function renderCandlestickChart(containerId, candleSeries, retryCount = 0) {
    const el = document.getElementById(containerId);
    if (!el) return;

    // Fallback if empty data provided
    if (!candleSeries || candleSeries.length === 0) {
        candleSeries = [];
        for (let i = 6; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            const dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            candleSeries.push({
                x: dateStr,
                y: [15 + i, 35 + (i * 2), 10, 28 + i]
            });
        }
    }

    if (!window.ApexCharts) {
        if (retryCount < 12) {
            setTimeout(() => renderCandlestickChart(containerId, candleSeries, retryCount + 1), 150);
            return;
        }
        // If CDN blocked by extension, render custom Native SVG Candlestick
        renderSvgCandlestickFallback(el, candleSeries);
        return;
    }

    if (candlestickChartObj) {
        try { candlestickChartObj.destroy(); } catch(e) {}
    }

    try {
        el.innerHTML = '';
        const options = {
            series: [{
                name: 'Daily Active Visitors Flow',
                data: candleSeries
            }],
            chart: {
                type: 'candlestick',
                height: 340,
                background: 'transparent',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif'
            },
            theme: { mode: 'dark' },
            plotOptions: {
                candlestick: {
                    colors: {
                        upward: '#10b981',    // Emerald Green for bullish growth
                        downward: '#ef4444'   // Crimson Red for off-peak traffic
                    },
                    wick: { useFillColor: true }
                }
            },
            xaxis: {
                type: 'category',
                labels: { style: { colors: '#71717a', fontSize: '11px' } },
                axisBorder: { color: 'rgba(255, 255, 255, 0.08)' },
                axisTicks: { color: 'rgba(255, 255, 255, 0.08)' }
            },
            yaxis: {
                tooltip: { enabled: true },
                labels: { style: { colors: '#71717a', fontSize: '11px' } }
            },
            grid: {
                borderColor: 'rgba(255, 255, 255, 0.04)',
                strokeDashArray: 3
            },
            tooltip: {
                theme: 'dark',
                custom: function({ seriesIndex, dataPointIndex, w }) {
                    const o = w.globals.seriesCandleO[seriesIndex][dataPointIndex];
                    const h = w.globals.seriesCandleH[seriesIndex][dataPointIndex];
                    const l = w.globals.seriesCandleL[seriesIndex][dataPointIndex];
                    const c = w.globals.seriesCandleC[seriesIndex][dataPointIndex];
                    return `<div style="padding: 10px 14px; background: #09090b; border: 1px solid #27272a; border-radius: 6px; font-size: 12px; font-family: Inter;">
                        <div style="font-weight: 700; color: #fff; margin-bottom: 6px;">Daily Visitor Metrics</div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; color: #a1a1aa;">
                            <div>Open: <strong style="color:#fff;">${o}</strong></div>
                            <div>High: <strong style="color:#10b981;">${h}</strong></div>
                            <div>Low: <strong style="color:#ef4444;">${l}</strong></div>
                            <div>Close: <strong style="color:#fff;">${c}</strong></div>
                        </div>
                    </div>`;
                }
            }
        };

        candlestickChartObj = new ApexCharts(el, options);
        candlestickChartObj.render();
    } catch (err) {
        console.warn('[Zanora Charts] ApexCharts fallback to SVG:', err);
        renderSvgCandlestickFallback(el, candleSeries);
    }
}

/* SVG Candlestick Renderer Fallback */
function renderSvgCandlestickFallback(el, series) {
    if (!el || !series || series.length === 0) return;
    
    let maxVal = 1;
    let minVal = 0;
    series.forEach(item => {
        const [o, h, l, c] = item.y;
        if (h > maxVal) maxVal = h;
    });
    maxVal = Math.ceil(maxVal * 1.15);

    const count = series.length;
    let candleSvg = `<svg width="100%" height="280" viewBox="0 0 ${count * 50} 260" preserveAspectRatio="none" style="display:block; overflow:visible;">`;
    
    series.forEach((item, idx) => {
        const [open, high, low, close] = item.y;
        const isBullish = close >= open;
        const color = isBullish ? '#10b981' : '#ef4444';
        
        const x = idx * 50 + 25;
        const scale = (val) => 220 - ((val / maxVal) * 200);
        
        const yHigh = scale(high);
        const yLow = scale(low);
        const yOpen = scale(open);
        const yClose = scale(close);
        
        const bodyTop = Math.min(yOpen, yClose);
        const bodyHeight = Math.max(4, Math.abs(yClose - yOpen));
        
        // Wick
        candleSvg += `<line x1="${x}" y1="${yHigh}" x2="${x}" y2="${yLow}" stroke="${color}" stroke-width="1.5" opacity="0.8"/>`;
        // Body
        candleSvg += `<rect x="${x - 10}" y="${bodyTop}" width="20" height="${bodyHeight}" rx="2" fill="${color}"/>`;
        // Label
        candleSvg += `<text x="${x}" y="250" text-anchor="middle" fill="#71717a" font-size="10" font-family="Inter">${item.x}</text>`;
    });
    
    candleSvg += `</svg>`;
    
    el.innerHTML = `
        <div style="padding: 16px; background: rgba(255,255,255,0.015); border-radius: 8px;">
            ${candleSvg}
        </div>
    `;
}

/* --------------------------------------------------------------------------
   4. WORLD COUNTRY TRAFFIC MAP (jsVectorMap + Interactive Heatmap Fallback)
   -------------------------------------------------------------------------- */
function renderCountryTrafficMap(containerId, countriesData, retryCount = 0) {
    const el = document.getElementById(containerId);
    if (!el) return;

    if (!countriesData || countriesData.length === 0) {
        countriesData = [
            { code: 'US', name: 'United States', count: 120, flag: '🇺🇸' },
            { code: 'IN', name: 'India', count: 85, flag: '🇮🇳' },
            { code: 'DE', name: 'Germany', count: 45, flag: '🇩🇪' },
            { code: 'GB', name: 'United Kingdom', count: 35, flag: '🇬🇧' },
            { code: 'JP', name: 'Japan', count: 30, flag: '🇯🇵' }
        ];
    }

    if (!window.jsVectorMap) {
        if (retryCount < 12) {
            setTimeout(() => renderCountryTrafficMap(containerId, countriesData, retryCount + 1), 150);
            return;
        }
        renderHeatmapFallback(el, countriesData);
        return;
    }

    el.innerHTML = '';
    const valuesObj = {};
    countriesData.forEach(c => {
        valuesObj[c.code] = c.count;
    });

    try {
        worldMapObj = new jsVectorMap({
            selector: '#' + containerId,
            map: 'world',
            backgroundColor: 'transparent',
            draggable: true,
            zoomButtons: false,
            zoomOnScroll: false,
            regionStyle: {
                initial: {
                    fill: '#18181b',
                    fillOpacity: 1,
                    stroke: '#27272a',
                    strokeWidth: 0.8
                },
                hover: {
                    fill: '#ffffff',
                    cursor: 'pointer'
                }
            },
            visualizeData: {
                scale: ['#27272a', '#ffffff'],
                values: valuesObj
            },
            onRegionTooltipShow(event, tooltip, code) {
                const count = valuesObj[code] || 0;
                const match = countriesData.find(c => c.code === code);
                const flag = match ? match.flag : '';
                const name = match ? match.name : code;
                tooltip.css({ backgroundColor: '#09090b', borderColor: '#27272a', color: '#ffffff', borderRadius: '6px', padding: '8px 12px' });
                tooltip.text(`${flag} ${name}: ${count.toLocaleString()} visitors`, true);
            }
        });
    } catch (err) {
        console.warn('[Zanora Charts] jsVectorMap fallback to Visual Map:', err);
        renderHeatmapFallback(el, countriesData);
    }
}

/* Heatmap & Vector Fallback */
function renderHeatmapFallback(el, countriesData) {
    if (!el || !countriesData) return;
    
    let total = countriesData.reduce((acc, c) => acc + (c.count || 0), 0);
    if (total === 0) total = 1;

    let itemsHtml = '';
    countriesData.slice(0, 7).forEach((c, idx) => {
        const pct = Math.round((c.count / total) * 100);
        itemsHtml += `
            <div style="margin-bottom: 12px;">
                <div style="display:flex; align-items:center; justify-content:space-between; font-size:12px; margin-bottom:4px;">
                    <span style="color:#fff; font-weight:600;"><span style="margin-right:6px;">${c.flag}</span>${c.name}</span>
                    <span style="color:#a1a1aa; font-family:monospace;">${c.count.toLocaleString()} (${pct}%)</span>
                </div>
                <div style="width:100%; height:6px; background:rgba(255,255,255,0.06); border-radius:3px; overflow:hidden;">
                    <div style="width:${pct}%; height:100%; background:#ffffff; border-radius:3px;"></div>
                </div>
            </div>
        `;
    });

    el.innerHTML = `
        <div style="padding: 20px; background: #09090b; border-radius: 8px; height: 100%; display: flex; flex-direction: column; justify-content: center;">
            <div style="font-size: 11px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;">
                <i class="fas fa-globe mr-1" style="color:#60a5fa;"></i> Geolocation Traffic Distribution
            </div>
            ${itemsHtml}
        </div>
    `;
}
