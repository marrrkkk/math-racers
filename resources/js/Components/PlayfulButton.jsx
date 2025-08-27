import { useState } from "react";
import soundEffects from "@/utils/soundEffects";

export default function PlayfulButton({
    children,
    onClick,
    disabled = false,
    variant = "primary",
    size = "normal",
    icon = null,
    soundEffect = "click",
    className = "",
    ...props
}) {
    const [isPressed, setIsPressed] = useState(false);

    const variants = {
        primary:
            "bg-gradient-to-r from-pink-400 to-purple-500 hover:from-pink-500 hover:to-purple-600",
        success:
            "bg-gradient-to-r from-green-400 to-blue-500 hover:from-green-500 hover:to-blue-600",
        warning:
            "bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600",
        racing: "bg-gradient-to-r from-red-400 to-pink-500 hover:from-red-500 hover:to-pink-600",
        fun: "bg-gradient-to-r from-purple-400 to-pink-500 hover:from-purple-500 hover:to-pink-600",
    };

    const sizes = {
        small: "px-4 py-2 text-sm",
        normal: "px-6 py-3 text-base",
        large: "px-8 py-4 text-lg",
        xl: "px-10 py-5 text-xl",
    };

    const handleClick = (e) => {
        if (disabled) return;

        setIsPressed(true);
        setTimeout(() => setIsPressed(false), 150);

        // Play sound effect
        if (soundEffect === "click") {
            soundEffects.playClick();
        } else if (soundEffect === "racing") {
            soundEffects.playRacerMove();
        } else if (soundEffect === "success") {
            soundEffects.playSuccess();
        } else if (soundEffect === "whoosh") {
            soundEffects.playWhoosh();
        }

        if (onClick) {
            onClick(e);
        }
    };

    const variantClass = variants[variant] || variants.primary;
    const sizeClass = sizes[size] || sizes.normal;

    return (
        <button
            {...props}
            onClick={handleClick}
            disabled={disabled}
            className={`
                relative overflow-hidden rounded-full font-bold text-white
                transition-all duration-300 ease-out transform
                shadow-playful hover:shadow-playful-lg
                ${variantClass} ${sizeClass}
                ${isPressed ? "scale-95" : "hover:scale-105"}
                ${disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer"}
                ${className}
            `}
        >
            {/* Sparkle effect overlay */}
            <div className="absolute inset-0 opacity-0 hover:opacity-100 transition-opacity duration-300">
                <div className="sparkle absolute inset-0"></div>
            </div>

            {/* Button content */}
            <span className="relative z-10 flex items-center justify-center space-x-2">
                {icon && <span className="text-lg">{icon}</span>}
                <span>{children}</span>
            </span>

            {/* Ripple effect */}
            {isPressed && (
                <div className="absolute inset-0 bg-white opacity-30 rounded-full animate-ping"></div>
            )}
        </button>
    );
}
