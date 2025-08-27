import { useState } from "react";

export default function Badge({
    type,
    earnedAt,
    data = {},
    size = "normal",
    showTooltip = true,
    animated = true,
}) {
    const [isHovered, setIsHovered] = useState(false);

    const badgeConfig = {
        completion: {
            icon: "🏁",
            color: "bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border-green-300",
            name: "Topic Master",
            hoverColor: "hover:from-green-200 hover:to-emerald-200",
        },
        speed: {
            icon: "⚡",
            color: "bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-800 border-yellow-300",
            name: "Speed Racer",
            hoverColor: "hover:from-yellow-200 hover:to-amber-200",
        },
        accuracy: {
            icon: "🎯",
            color: "bg-gradient-to-r from-blue-100 to-sky-100 text-blue-800 border-blue-300",
            name: "Sharp Shooter",
            hoverColor: "hover:from-blue-200 hover:to-sky-200",
        },
        streak: {
            icon: "🔥",
            color: "bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border-red-300",
            name: "Hot Streak",
            hoverColor: "hover:from-red-200 hover:to-rose-200",
        },
        perfectscore: {
            icon: "⭐",
            color: "bg-gradient-to-r from-purple-100 to-violet-100 text-purple-800 border-purple-300",
            name: "Perfect Score",
            hoverColor: "hover:from-purple-200 hover:to-violet-200",
        },
        dedication: {
            icon: "💪",
            color: "bg-gradient-to-r from-orange-100 to-amber-100 text-orange-800 border-orange-300",
            name: "Dedicated Learner",
            hoverColor: "hover:from-orange-200 hover:to-amber-200",
        },
    };

    const config = badgeConfig[type] || badgeConfig["completion"];

    const sizeClasses = {
        small: "px-2 py-1 text-xs",
        normal: "px-3 py-1 text-sm",
        large: "px-4 py-2 text-base",
    };

    const sizeClass = sizeClasses[size] || sizeClasses.normal;

    const formatDate = (dateString) => {
        try {
            return new Date(dateString).toLocaleDateString();
        } catch {
            return "Recently";
        }
    };

    const tooltipText = showTooltip
        ? `${config.name} - Earned ${formatDate(earnedAt)}${
              data.description ? ` - ${data.description}` : ""
          }`
        : "";

    return (
        <div
            className={`
                inline-flex items-center rounded-full font-medium border-2 cursor-default
                transition-all duration-300 transform
                ${config.color} ${config.hoverColor} ${sizeClass}
                ${animated ? "hover:scale-110 hover:shadow-playful" : ""}
                ${isHovered ? "animate-pulse" : ""}
            `}
            title={tooltipText}
            onMouseEnter={() => setIsHovered(true)}
            onMouseLeave={() => setIsHovered(false)}
        >
            <span
                className={`mr-1 text-lg ${
                    animated && isHovered ? "animate-bounce" : ""
                }`}
            >
                {config.icon}
            </span>
            <span className="font-bold">{config.name}</span>
            {data.level && (
                <span className="ml-1 text-xs opacity-75 bg-white bg-opacity-50 px-1 rounded-full">
                    Lv.{data.level}
                </span>
            )}

            {/* Sparkle effect for special badges */}
            {(type === "perfectscore" || type === "streak") && isHovered && (
                <div className="absolute -top-1 -right-1 text-xs animate-ping">
                    ✨
                </div>
            )}
        </div>
    );
}
