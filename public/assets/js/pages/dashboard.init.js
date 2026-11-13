// Function to retrieve and process chart colors from data-colors attribute
function getChartColorsArray(e) {
    const element = document.getElementById(e);
    if (null !== element) {
        const colorsAttr = element.getAttribute("data-colors");
        if (colorsAttr) {
            return JSON.parse(colorsAttr).map(function (color) {
                const trimmedColor = color.replace(" ", "");
                if (trimmedColor.indexOf(",") === -1) {
                    // Single CSS variable
                    const cssValue = getComputedStyle(document.documentElement).getPropertyValue(trimmedColor);
                    return cssValue || trimmedColor;
                } else {
                    // RGBA with CSS variable and opacity
                    const colorParts = trimmedColor.split(",");
                    if (colorParts.length !== 2) {
                        return color;
                    }
                    const cssVar = getComputedStyle(document.documentElement).getPropertyValue(colorParts[0]);
                    return "rgba(" + cssVar + "," + colorParts[1] + ")";
                }
            });
        }
    }
}

// Stacked Column Chart (Bar Chart)
var linechartBasicColors = getChartColorsArray("stacked-column-chart");
if (linechartBasicColors) {
    var options = {
        chart: {
            height: 360,
            type: "bar",
            stacked: true,
            toolbar: {
                show: false
            },
            zoom: {
                enabled: true
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: "15%",
                endingShape: "rounded",
                borderRadius: 5
            }
        },
        dataLabels: {
            enabled: false
        },
        series: [{
            name: "Series A",
            data: [44, 55, 41, 67, 22, 43, 36, 52, 24, 18, 36, 48]
        }, {
            name: "Series B",
            data: [13, 23, 20, 8, 13, 27, 18, 22, 10, 16, 24, 22]
        }, {
            name: "Series C",
            data: [11, 17, 15, 15, 21, 14, 11, 18, 17, 12, 20, 18]
        }],
        xaxis: {
            categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
        },
        colors: linechartBasicColors,
        legend: {
            position: "bottom"
        },
        fill: {
            opacity: 1
        }
    };

    var chart = new ApexCharts(document.querySelector("#stacked-column-chart"), options);
    chart.render();
}

// Radial Bar Chart
var radialbarColors = getChartColorsArray("radialBar-chart");
if (radialbarColors) {
    var options = {
        chart: {
            height: 200,
            type: "radialBar",
            offsetY: -10
        },
        plotOptions: {
            radialBar: {
                startAngle: -135,
                endAngle: 135,
                dataLabels: {
                    name: {
                        fontSize: "13px",
                        color: undefined,
                        offsetY: 60
                    },
                    value: {
                        offsetY: 22,
                        fontSize: "16px",
                        color: undefined,
                        formatter: function (val) {
                            return val + "%";
                        }
                    }
                }
            }
        },
        colors: radialbarColors,
        fill: {
            type: "gradient",
            gradient: {
                shade: "dark",
                shadeIntensity: 0.15,
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 50, 65, 91]
            }
        },
        stroke: {
            dashArray: 4
        },
        series: [67],
        labels: ["Series A"]
    };

    var chart = new ApexCharts(document.querySelector("#radialBar-chart"), options);
    chart.render();
}
