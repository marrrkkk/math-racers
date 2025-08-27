import { useState, useEffect } from "react";

export default function ProgressChart({
    data = [],
    type = "line",
    title = "",
    height = 200,
    color = "blue",
}) {
    const [animatedData, setAnimatedData] = useState([]);

    useEffect(() => {
        // Animate chart data on mount
        const timer = setTimeout(() => {
            setAnimatedData(data);
        }, 100);
        return () => clearTimeout(timer);
    }, [data]);

    const colorClasses = {
        blue: {
            primary: "#3B82F6",
            secondary: "#DBEAFE",
            gradient: "from-blue-500 to-blue-600",
        },
        green: {
            primary: "#10B981",
            secondary: "#D1FAE5",
            gradient: "from-green-500 to-green-600",
        },
        purple: {
            primary: "#8B5CF6",
            secondary: "#EDE9FE",
            gradient: "from-purple-500 to-purple-600",
        },
        orange: {
            primary: "#F59E0B",
            secondary: "#FEF3C7",
            gradient: "from-orange-500 to-orange-600",
        },
    };

    const colors = colorClasses[color] || colorClasses.blue;

    if (!data || data.length === 0) {
        return (
            <div className="bg-white border border-gray-200 rounded-xl p-6">
                <h4 className="text-lg font-semibold text-gray-900 mb-4">
                    {title}
                </h4>
                <div className="flex items-center justify-center h-48 text-gray-500">
                    <div className="text-center">
                        <div className="text-4xl mb-2">📊</div>
                        <p>No data available</p>
                    </div>
                </div>
            </div>
        );
    }

    const maxValue = Math.max(...data.map((d) => d.value || 0));
    const minValue = Math.min(...data.map((d) => d.value || 0));
    const range = maxValue - minValue || 1;

    if (type === "bar") {
        return (
            <div className="bg-white border border-gray-200 rounded-xl p-6">
                {title && (
                    <h4 className="text-lg font-semibold text-gray-900 mb-4">
                        {title}
                    </h4>
                )}
                <div className="space-y-4" style={{ height: `${height}px` }}>
                    {animatedData.map((item, index) => {
                        const percentage =
                            maxValue > 0 ? (item.value / maxValue) * 100 : 0;
                        return (
                            <div key={index} className="flex items-center">
                                <div className="w-20 text-sm font-medium text-gray-700 truncate">
                                    {item.label}
                                </div>
                                <div className="flex-1 mx-3">
                                    <div className="bg-gray-200 rounded-full h-6 relative overflow-hidden">
                                        <div
                                            className={`h-full bg-gradient-to-r ${colors.gradient} transition-all duration-1000 ease-out rounded-full flex items-center justify-end pr-2`}
                                            style={{ width: `${percentage}%` }}
                                        >
                                            <span className="text-xs font-medium text-white">
                                                {item.value}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        );
    }

    if (type === "donut") {
        const total = data.reduce((sum, item) => sum + (item.value || 0), 0);
        let cumulativePercentage = 0;

        return (
            <div className="bg-white border border-gray-200 rounded-xl p-6">
                {title && (
                    <h4 className="text-lg font-semibold text-gray-900 mb-4">
                        {title}
                    </h4>
                )}
                <div className="flex items-center justify-center">
                    <div
                        className="relative"
                        style={{ width: height, height: height }}
                    >
                        <svg
                            width={height}
                            height={height}
                            className="transform -rotate-90"
                        >
                            <circle
                                cx={height / 2}
                                cy={height / 2}
                                r={height / 2 - 20}
                                fill="none"
                                stroke="#E5E7EB"
                                strokeWidth="20"
                            />
                            {animatedData.map((item, index) => {
                                const percentage =
                                    total > 0 ? (item.value / total) * 100 : 0;
                                const circumference =
                                    2 * Math.PI * (height / 2 - 20);
                                const strokeDasharray = `${
                                    (percentage / 100) * circumference
                                } ${circumference}`;
                                const strokeDashoffset = -(
                                    (cumulativePercentage / 100) *
                                    circumference
                                );

                                const result = (
                                    <circle
                                        key={index}
                                        cx={height / 2}
                                        cy={height / 2}
                                        r={height / 2 - 20}
                                        fill="none"
                                        stroke={colors.primary}
                                        strokeWidth="20"
                                        strokeDasharray={strokeDasharray}
                                        strokeDashoffset={strokeDashoffset}
                                        className="transition-all duration-1000 ease-out"
                                        style={{
                                            filter: `hue-rotate(${
                                                index * 60
                                            }deg)`,
                                        }}
                                    />
                                );

                                cumulativePercentage += percentage;
                                return result;
                            })}
                        </svg>
                        <div className="absolute inset-0 flex items-center justify-center">
                            <div className="text-center">
                                <div className="text-2xl font-bold text-gray-900">
                                    {total}
                                </div>
                                <div className="text-sm text-gray-500">
                                    Total
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="mt-4 grid grid-cols-2 gap-2">
                    {data.map((item, index) => (
                        <div key={index} className="flex items-center text-sm">
                            <div
                                className="w-3 h-3 rounded-full mr-2"
                                style={{
                                    backgroundColor: colors.primary,
                                    filter: `hue-rotate(${index * 60}deg)`,
                                }}
                            ></div>
                            <span className="text-gray-700">
                                {item.label}: {item.value}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    // Default line chart
    const chartHeight = height - 60;
    const chartWidth = 400;
    const padding = 40;

    return (
        <div className="bg-white border border-gray-200 rounded-xl p-6">
            {title && (
                <h4 className="text-lg font-semibold text-gray-900 mb-4">
                    {title}
                </h4>
            )}
            <div className="overflow-x-auto">
                <svg width={chartWidth} height={height} className="min-w-full">
                    {/* Grid lines */}
                    {[0, 25, 50, 75, 100].map((y) => (
                        <line
                            key={y}
                            x1={padding}
                            y1={padding + (chartHeight * (100 - y)) / 100}
                            x2={chartWidth - padding}
                            y2={padding + (chartHeight * (100 - y)) / 100}
                            stroke="#E5E7EB"
                            strokeWidth="1"
                            strokeDasharray="2,2"
                        />
                    ))}

                    {/* Data line */}
                    {animatedData.length > 1 && (
                        <polyline
                            fill="none"
                            stroke={colors.primary}
                            strokeWidth="3"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            points={animatedData
                                .map((item, index) => {
                                    const x =
                                        padding +
                                        (index * (chartWidth - 2 * padding)) /
                                            (animatedData.length - 1);
                                    const y =
                                        padding +
                                        (chartHeight *
                                            (maxValue - item.value)) /
                                            range;
                                    return `${x},${y}`;
                                })
                                .join(" ")}
                            className="transition-all duration-1000 ease-out"
                        />
                    )}

                    {/* Data points */}
                    {animatedData.map((item, index) => {
                        const x =
                            padding +
                            (index * (chartWidth - 2 * padding)) /
                                (animatedData.length - 1 || 1);
                        const y =
                            padding +
                            (chartHeight * (maxValue - item.value)) / range;
                        return (
                            <g key={index}>
                                <circle
                                    cx={x}
                                    cy={y}
                                    r="6"
                                    fill={colors.primary}
                                    className="transition-all duration-1000 ease-out"
                                />
                                <text
                                    x={x}
                                    y={height - 10}
                                    textAnchor="middle"
                                    className="text-xs fill-gray-600"
                                >
                                    {item.label}
                                </text>
                            </g>
                        );
                    })}

                    {/* Y-axis labels */}
                    {[0, 25, 50, 75, 100].map((y) => (
                        <text
                            key={y}
                            x={padding - 10}
                            y={padding + (chartHeight * (100 - y)) / 100 + 4}
                            textAnchor="end"
                            className="text-xs fill-gray-600"
                        >
                            {Math.round((y / 100) * maxValue)}
                        </text>
                    ))}
                </svg>
            </div>
        </div>
    );
}
