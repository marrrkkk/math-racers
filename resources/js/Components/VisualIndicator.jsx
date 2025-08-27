import { useState, useEffect } from "react";

export default function VisualIndicator({
    type = "circular",
    value = 0,
    maxValue = 100,
    size = "medium",
    color = "blue",
    label = "",
    showValue = true,
    animated = true,
    icon = null,
}) {
    const [animatedValue, setAnimatedValue] = useState(0);

    useEffect(() => {
        if (animated) {
            const timer = setTimeout(() => {
                setAnimatedValue(value);
            }, 100);
            return () => clearTimeout(timer);
        } else {
            setAnimatedValue(value);
        }
    }, [value, animated]);

    const percentage =
        maxValue > 0 ? Math.min((animatedValue / maxValue) * 100, 100) : 0;

    const sizeClasses = {
        small: { container: "w-16 h-16", text: "text-xs", stroke: "4" },
        medium: { container: "w-24 h-24", text: "text-sm", stroke: "6" },
        large: { container: "w-32 h-32", text: "text-lg", stroke: "8" },
    };

    const colorClasses = {
        blue: {
            primary: "#3B82F6",
            secondary: "#DBEAFE",
            gradient: "from-blue-400 to-blue-600",
            text: "text-blue-600",
        },
        green: {
            primary: "#10B981",
            secondary: "#D1FAE5",
            gradient: "from-green-400 to-green-600",
            text: "text-green-600",
        },
        purple: {
            primary: "#8B5CF6",
            secondary: "#EDE9FE",
            gradient: "from-purple-400 to-purple-600",
            text: "text-purple-600",
        },
        orange: {
            primary: "#F59E0B",
            secondary: "#FEF3C7",
            gradient: "from-orange-400 to-orange-600",
            text: "text-orange-600",
        },
        red: {
            primary: "#EF4444",
            secondary: "#FEE2E2",
            gradient: "from-red-400 to-red-600",
            text: "text-red-600",
        },
        yellow: {
            primary: "#F59E0B",
            secondary: "#FEF3C7",
            gradient: "from-yellow-400 to-yellow-600",
            text: "text-yellow-600",
        },
    };

    const sizeConfig = sizeClasses[size] || sizeClasses.medium;
    const colors = colorClasses[color] || colorClasses.blue;

    if (type === "circular") {
        const radius = 45;
        const circumference = 2 * Math.PI * radius;
        const strokeDasharray = circumference;
        const strokeDashoffset =
            circumference - (percentage / 100) * circumference;

        return (
            <div className="flex flex-col items-center">
                <div className={`relative ${sizeConfig.container}`}>
                    <svg
                        className="w-full h-full transform -rotate-90"
                        viewBox="0 0 100 100"
                    >
                        {/* Background circle */}
                        <circle
                            cx="50"
                            cy="50"
                            r={radius}
                            fill="none"
                            stroke={colors.secondary}
                            strokeWidth={sizeConfig.stroke}
                        />
                        {/* Progress circle */}
                        <circle
                            cx="50"
                            cy="50"
                            r={radius}
                            fill="none"
                            stroke={colors.primary}
                            strokeWidth={sizeConfig.stroke}
                            strokeDasharray={strokeDasharray}
                            strokeDashoffset={strokeDashoffset}
                            strokeLinecap="round"
                            className={
                                animated
                                    ? "transition-all duration-1000 ease-out"
                                    : ""
                            }
                        />
                    </svg>
                    <div className="absolute inset-0 flex items-center justify-center">
                        <div className="text-center">
                            {icon && <div className="text-lg mb-1">{icon}</div>}
                            {showValue && (
                                <div
                                    className={`font-bold ${colors.text} ${sizeConfig.text}`}
                                >
                                    {Math.round(percentage)}%
                                </div>
                            )}
                        </div>
                    </div>
                </div>
                {label && (
                    <div
                        className={`mt-2 text-center text-gray-600 ${sizeConfig.text}`}
                    >
                        {label}
                    </div>
                )}
            </div>
        );
    }

    if (type === "linear") {
        return (
            <div className="w-full">
                {label && (
                    <div className="flex justify-between items-center mb-2">
                        <span className="text-sm font-medium text-gray-700">
                            {label}
                        </span>
                        {showValue && (
                            <span
                                className={`text-sm font-bold ${colors.text}`}
                            >
                                {animatedValue}/{maxValue}
                            </span>
                        )}
                    </div>
                )}
                <div className="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div
                        className={`h-full bg-gradient-to-r ${colors.gradient} transition-all duration-1000 ease-out rounded-full`}
                        style={{ width: `${percentage}%` }}
                    ></div>
                </div>
                {showValue && (
                    <div className="text-center mt-1">
                        <span className={`text-xs ${colors.text}`}>
                            {Math.round(percentage)}%
                        </span>
                    </div>
                )}
            </div>
        );
    }

    if (type === "gauge") {
        const gaugeRadius = 40;
        const startAngle = -90;
        const endAngle = 90;
        const totalAngle = endAngle - startAngle;
        const currentAngle = startAngle + (percentage / 100) * totalAngle;

        const startX =
            50 + gaugeRadius * Math.cos((startAngle * Math.PI) / 180);
        const startY =
            50 + gaugeRadius * Math.sin((startAngle * Math.PI) / 180);
        const endX = 50 + gaugeRadius * Math.cos((endAngle * Math.PI) / 180);
        const endY = 50 + gaugeRadius * Math.sin((endAngle * Math.PI) / 180);
        const currentX =
            50 + gaugeRadius * Math.cos((currentAngle * Math.PI) / 180);
        const currentY =
            50 + gaugeRadius * Math.sin((currentAngle * Math.PI) / 180);

        return (
            <div className="flex flex-col items-center">
                <div className={`relative ${sizeConfig.container}`}>
                    <svg className="w-full h-full" viewBox="0 0 100 100">
                        {/* Background arc */}
                        <path
                            d={`M ${startX} ${startY} A ${gaugeRadius} ${gaugeRadius} 0 0 1 ${endX} ${endY}`}
                            fill="none"
                            stroke={colors.secondary}
                            strokeWidth={sizeConfig.stroke}
                            strokeLinecap="round"
                        />
                        {/* Progress arc */}
                        <path
                            d={`M ${startX} ${startY} A ${gaugeRadius} ${gaugeRadius} 0 0 1 ${currentX} ${currentY}`}
                            fill="none"
                            stroke={colors.primary}
                            strokeWidth={sizeConfig.stroke}
                            strokeLinecap="round"
                            className={
                                animated
                                    ? "transition-all duration-1000 ease-out"
                                    : ""
                            }
                        />
                        {/* Center dot */}
                        <circle cx="50" cy="50" r="2" fill={colors.primary} />
                        {/* Needle */}
                        <line
                            x1="50"
                            y1="50"
                            x2={currentX}
                            y2={currentY}
                            stroke={colors.primary}
                            strokeWidth="2"
                            strokeLinecap="round"
                            className={
                                animated
                                    ? "transition-all duration-1000 ease-out"
                                    : ""
                            }
                        />
                    </svg>
                    <div className="absolute bottom-2 left-1/2 transform -translate-x-1/2">
                        {showValue && (
                            <div
                                className={`font-bold ${colors.text} ${sizeConfig.text} text-center`}
                            >
                                {Math.round(percentage)}%
                            </div>
                        )}
                    </div>
                </div>
                {label && (
                    <div
                        className={`mt-2 text-center text-gray-600 ${sizeConfig.text}`}
                    >
                        {label}
                    </div>
                )}
            </div>
        );
    }

    if (type === "steps") {
        const steps = 5;
        const stepValue = maxValue / steps;
        const completedSteps = Math.floor(animatedValue / stepValue);

        return (
            <div className="w-full">
                {label && (
                    <div className="flex justify-between items-center mb-3">
                        <span className="text-sm font-medium text-gray-700">
                            {label}
                        </span>
                        {showValue && (
                            <span
                                className={`text-sm font-bold ${colors.text}`}
                            >
                                {animatedValue}/{maxValue}
                            </span>
                        )}
                    </div>
                )}
                <div className="flex items-center space-x-2">
                    {Array.from({ length: steps }, (_, index) => (
                        <div key={index} className="flex-1">
                            <div
                                className={`h-3 rounded-full transition-all duration-500 ${
                                    index < completedSteps
                                        ? `bg-gradient-to-r ${colors.gradient}`
                                        : index === completedSteps &&
                                          animatedValue % stepValue > 0
                                        ? `bg-gradient-to-r ${colors.gradient} opacity-50`
                                        : "bg-gray-200"
                                }`}
                                style={{
                                    width:
                                        index === completedSteps &&
                                        animatedValue % stepValue > 0
                                            ? `${
                                                  ((animatedValue % stepValue) /
                                                      stepValue) *
                                                  100
                                              }%`
                                            : "100%",
                                }}
                            ></div>
                        </div>
                    ))}
                </div>
                <div className="flex justify-between mt-1 text-xs text-gray-500">
                    {Array.from({ length: steps + 1 }, (_, index) => (
                        <span key={index}>{Math.round(index * stepValue)}</span>
                    ))}
                </div>
            </div>
        );
    }

    // Default to circular if type is not recognized
    return null;
}
