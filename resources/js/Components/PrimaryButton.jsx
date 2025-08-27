export default function PrimaryButton({
    className = "",
    disabled,
    children,
    variant = "default",
    size = "normal",
    ...props
}) {
    const variants = {
        default:
            "bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700",
        playful: "btn-playful",
        success:
            "bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700",
        warning:
            "bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700",
        racing: "racing-button racing-stripes",
    };

    const sizes = {
        small: "px-3 py-2 text-xs",
        normal: "px-4 py-2 text-sm",
        large: "px-6 py-3 text-base",
        xl: "px-8 py-4 text-lg",
    };

    const baseClasses = `
        inline-flex items-center justify-center rounded-xl border-0 font-bold uppercase tracking-wide 
        text-white transition-all duration-300 ease-out transform hover:scale-105 hover:shadow-playful
        focus:outline-none focus:ring-4 focus:ring-opacity-50 active:scale-95
        ${
            disabled
                ? "opacity-50 cursor-not-allowed transform-none hover:scale-100"
                : "cursor-pointer"
        }
    `;

    const variantClass = variants[variant] || variants.default;
    const sizeClass = sizes[size] || sizes.normal;

    return (
        <button
            {...props}
            className={`${baseClasses} ${variantClass} ${sizeClass} ${className}`}
            disabled={disabled}
        >
            <span className="relative z-10 flex items-center justify-center">
                {children}
            </span>
        </button>
    );
}
