export default function ProgressBar({
    percentage,
    color = "blue",
    height = "h-3",
    showLabel = false,
    label = "",
    animated = true,
}) {
    const colorClasses = {
        blue: "bg-blue-500",
        green: "bg-green-500",
        yellow: "bg-yellow-500",
        red: "bg-red-500",
        purple: "bg-purple-500",
        orange: "bg-orange-500",
    };

    const bgColorClass = colorClasses[color] || colorClasses.blue;
    const clampedPercentage = Math.min(Math.max(percentage, 0), 100);

    return (
        <div className="w-full">
            {showLabel && (
                <div className="flex justify-between text-sm mb-1">
                    <span className="text-gray-600">{label}</span>
                    <span className="font-medium">{clampedPercentage}%</span>
                </div>
            )}
            <div className={`w-full bg-gray-200 rounded-full ${height}`}>
                <div
                    className={`${height} rounded-full ${bgColorClass} ${
                        animated ? "transition-all duration-500 ease-out" : ""
                    }`}
                    style={{ width: `${clampedPercentage}%` }}
                ></div>
            </div>
        </div>
    );
}
