import { useState, useEffect } from "react";

export default function StatCard({
    title,
    value,
    previousValue = null,
    icon = null,
    color = "blue",
    size = "medium",
    showTrend = false,
    animated = true,
    suffix = "",
    prefix = "",
    description = null,
    onClick = null,
}) {
    const [animatedValue, setAnimatedValue] = useState(0);
    const [isVisible, setIsVisible] = useState(false);

    useEffect(() => {
        setIsVisible(true);
        if (animated && typeof value === "number") {
            const duration = 1000;
            const steps = 60;
            const increment = value / steps;
            let currentValue = 0;

            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= value) {
                    currentValue = value;
                    clearInterval(timer);
                }
                setAnimatedValue(currentValue);
            }, duration / steps);

            return () => clearInterval(timer);
        } else {
            setAnimatedValue(value);
        }
    }, [value, animated]);

    const colorClasses = {
        blue: {
            bg: "bg-gradient-to-br from-blue-50 to-blue-100",
            border: "border-blue-200",
            text: "text-blue-600",
            icon: "text-blue-500",
            trend: {
                up: "text-green-600 bg-green-100",
                down: "text-red-600 bg-red-100",
                neutral: "text-gray-600 bg-gray-100",
            },
        },
        green: {
            bg: "bg-gradient-to-br from-green-50 to-green-100",
            border: "border-green-200",
            text: "text-green-600",
            icon: "text-green-500",
            trend: {
                up: "text-green-600 bg-green-100",
                down: "text-red-600 bg-red-100",
                neutral: "text-gray-600 bg-gray-100",
            },
        },
        purple: {
            bg: "bg-gradient-to-br from-purple-50 to-purple-100",
            border: "border-purple-200",
            text: "text-purple-600",
            icon: "text-purple-500",
            trend: {
                up: "text-green-600 bg-green-100",
                down: "text-red-600 bg-red-100",
                neutral: "text-gray-600 bg-gray-100",
            },
        },
        orange: {
            bg: "bg-gradient-to-br from-orange-50 to-orange-100",
            border: "border-orange-200",
            text: "text-orange-600",
            icon: "text-orange-500",
            trend: {
                up: "text-green-600 bg-green-100",
                down: "text-red-600 bg-red-100",
                neutral: "text-gray-600 bg-gray-100",
            },
        },
        yellow: {
            bg: "bg-gradient-to-br from-yellow-50 to-yellow-100",
            border: "border-yellow-200",
            text: "text-yellow-600",
            icon: "text-yellow-500",
            trend: {
                up: "text-green-600 bg-green-100",
                down: "text-red-600 bg-red-100",
                neutral: "text-gray-600 bg-gray-100",
            },
        },
        red: {
            bg: "bg-gradient-to-br from-red-50 to-red-100",
            border: "border-red-200",
            text: "text-red-600",
            icon: "text-red-500",
            trend: {
                up: "text-green-600 bg-green-100",
                down: "text-red-600 bg-red-100",
                neutral: "text-gray-600 bg-gray-100",
            },
        },
    };

    const sizeClasses = {
        small: {
            container: "p-3",
            title: "text-xs",
            value: "text-lg",
            icon: "text-lg",
            description: "text-xs",
        },
        medium: {
            container: "p-4",
            title: "text-sm",
            value: "text-2xl",
            icon: "text-2xl",
            description: "text-sm",
        },
        large: {
            container: "p-6",
            title: "text-base",
            value: "text-3xl",
            icon: "text-3xl",
            description: "text-base",
        },
    };

    const colors = colorClasses[color] || colorClasses.blue;
    const sizes = sizeClasses[size] || sizeClasses.medium;

    // Calculate trend
    let trend = null;
    if (
        showTrend &&
        previousValue !== null &&
        typeof value === "number" &&
        typeof previousValue === "number"
    ) {
        const change = value - previousValue;
        const changePercent =
            previousValue !== 0 ? Math.abs((change / previousValue) * 100) : 0;

        if (change > 0) {
            trend = {
                direction: "up",
                icon: "↗️",
                text: `+${changePercent.toFixed(1)}%`,
                class: colors.trend.up,
            };
        } else if (change < 0) {
            trend = {
                direction: "down",
                icon: "↘️",
                text: `-${changePercent.toFixed(1)}%`,
                class: colors.trend.down,
            };
        } else {
            trend = {
                direction: "neutral",
                icon: "➡️",
                text: "0%",
                class: colors.trend.neutral,
            };
        }
    }

    const displayValue =
        typeof animatedValue === "number"
            ? Math.round(animatedValue).toLocaleString()
            : animatedValue;

    return (
        <div
            className={`
                ${colors.bg} ${colors.border} border rounded-xl ${
                sizes.container
            }
                ${onClick ? "cursor-pointer hover:shadow-lg" : ""}
                ${
                    isVisible
                        ? "transform translate-y-0 opacity-100"
                        : "transform translate-y-4 opacity-0"
                }
                transition-all duration-500 ease-out
            `}
            onClick={onClick}
        >
            <div className="flex items-start justify-between">
                <div className="flex-1">
                    <div
                        className={`${colors.text} ${sizes.title} font-medium mb-1`}
                    >
                        {title}
                    </div>
                    <div
                        className={`${colors.text} ${sizes.value} font-bold mb-2`}
                    >
                        {prefix}
                        {displayValue}
                        {suffix}
                    </div>
                    {description && (
                        <div className={`text-gray-600 ${sizes.description}`}>
                            {description}
                        </div>
                    )}
                    {trend && (
                        <div className="mt-2">
                            <span
                                className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${trend.class}`}
                            >
                                <span className="mr-1">{trend.icon}</span>
                                {trend.text}
                            </span>
                        </div>
                    )}
                </div>
                {icon && (
                    <div
                        className={`${colors.icon} ${sizes.icon} ml-3 flex-shrink-0`}
                    >
                        {icon}
                    </div>
                )}
            </div>
        </div>
    );
}
