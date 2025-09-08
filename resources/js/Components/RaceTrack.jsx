import { useState, useEffect } from "react";

export default function RaceTrack({
    racerProgress = 0,
    isAnimating = false,
    showCelebration = false,
    className = "",
}) {
    const [animationClass, setAnimationClass] = useState("");
    const [sparkles, setSparkles] = useState([]);

    useEffect(() => {
        if (isAnimating) {
            setAnimationClass("celebrate-bounce");

            // Create sparkle effects
            const newSparkles = Array.from({ length: 5 }, (_, i) => ({
                id: i,
                right: Math.random() * 80 + 10,
                delay: Math.random() * 0.5,
            }));
            setSparkles(newSparkles);

            const timer = setTimeout(() => {
                setAnimationClass("");
                setSparkles([]);
            }, 2000);
            return () => clearTimeout(timer);
        }
    }, [isAnimating]);

    const getProgressMessage = (progress) => {
        if (progress >= 90) return "🏆 Almost there! You're winning!";
        if (progress >= 75) return "🚀 Speeding ahead! Great job!";
        if (progress >= 50) return "⚡ Halfway there! Keep going!";
        if (progress >= 25) return "🏎️ Good start! You're racing!";
        return "🏁 Ready to race? Let's go!";
    };

    return (
        <div
            className={`racing-card-enhanced p-6 relative overflow-hidden ${className}`}
        >
            {/* Floating sparkles during animation */}
            {sparkles.map((sparkle) => (
                <div
                    key={sparkle.id}
                    className="absolute text-2xl animate-bounce pointer-events-none"
                    style={{
                        right: `${sparkle.right}%`,
                        top: "20%",
                        animationDelay: `${sparkle.delay}s`,
                    }}
                >
                    ✨
                </div>
            ))}

            <div className="text-center mb-4">
                <h3 className="text-2xl font-bold text-white flex items-center justify-center float-animation">
                    <span className="mr-2 text-3xl">🏁</span>
                    <span className="racing-stripes px-4 py-1 rounded-full">
                        Race Track
                    </span>
                    <span className="ml-2 text-3xl">🏁</span>
                </h3>
            </div>

            {/* Track */}
            <div className="relative bg-gradient-to-l from-gray-800 to-gray-900 rounded-lg h-24 mb-4 overflow-hidden shadow-playful">
                {/* Animated track lines */}
                <div className="absolute inset-0 flex items-center">
                    <div className="w-full border-t-4 border-dashed border-yellow-400 opacity-60 animate-pulse"></div>
                </div>

                {/* Start line with animation */}
                <div className="absolute right-0 top-0 h-full w-3 bg-gradient-to-l from-white to-gray-200 opacity-90">
                    <div className="absolute inset-0 bg-white animate-pulse"></div>
                </div>
                <div className="absolute right-4 top-1/2 transform -translate-y-1/2 text-white text-xs font-bold bg-black bg-opacity-50 px-2 py-1 rounded">
                    START
                </div>

                {/* Finish line with checkered pattern */}
                <div className="absolute left-0 top-0 h-full w-3 bg-gradient-to-r from-white to-gray-200 opacity-90 racing-stripes"></div>
                <div className="absolute left-4 top-1/2 transform -translate-y-1/2 text-white text-lg animate-bounce">
                    🏁
                </div>

                {/* Enhanced progress markers */}
                <div className="absolute top-0 right-1/4 h-full w-1 bg-white opacity-40">
                    <div className="absolute top-1/2 transform -translate-y-1/2 -translate-x-1/2 text-xs text-white">
                        25%
                    </div>
                </div>
                <div className="absolute top-0 right-1/2 h-full w-1 bg-white opacity-40">
                    <div className="absolute top-1/2 transform -translate-y-1/2 -translate-x-1/2 text-xs text-white">
                        50%
                    </div>
                </div>
                <div className="absolute top-0 right-3/4 h-full w-1 bg-white opacity-40">
                    <div className="absolute top-1/2 transform -translate-y-1/2 -translate-x-1/2 text-xs text-white">
                        75%
                    </div>
                </div>

                {/* Enhanced racer with trail effect */}
                <div
                    className={`absolute top-1/2 transform -translate-y-1/2 text-5xl transition-all duration-1000 ease-out ${animationClass} z-10`}
                    style={{
                        right: `${Math.max(2, Math.min(racerProgress, 88))}%`,
                    }}
                >
                    🏎️
                </div>

                {/* Speed trail effect */}
                {racerProgress > 0 && (
                    <div
                        className="absolute top-1/2 transform -translate-y-1/2 text-2xl opacity-50 transition-all duration-1000"
                        style={{
                            right: `${Math.max(
                                0,
                                Math.min(racerProgress - 5, 85)
                            )}%`,
                        }}
                    >
                        💨
                    </div>
                )}

                {/* Celebration effects */}
                {showCelebration && (
                    <div className="absolute inset-0 flex items-center justify-center">
                        <div className="text-6xl celebrate-bounce">🎉</div>
                        <div
                            className="absolute top-2 left-1/4 text-3xl celebrate-pulse"
                            style={{ animationDelay: "0.2s" }}
                        >
                            🎊
                        </div>
                        <div
                            className="absolute bottom-2 right-1/4 text-3xl celebrate-pulse"
                            style={{ animationDelay: "0.4s" }}
                        >
                            🌟
                        </div>
                    </div>
                )}
            </div>

            {/* Enhanced race position indicator */}
            <div className="text-center text-white">
                <div className="text-lg font-bold mb-2 animate-pulse">
                    {getProgressMessage(racerProgress)}
                </div>
                <div className="text-sm opacity-90 mb-3">
                    🏁 Position: {Math.round(racerProgress)}% complete
                </div>

                {/* Enhanced progress bar */}
                <div className="relative w-full bg-white bg-opacity-20 rounded-full h-4 shadow-inner">
                    <div
                        className="bg-gradient-to-l from-yellow-400 via-orange-500 to-red-500 h-4 rounded-full transition-all duration-1000 shadow-playful relative overflow-hidden"
                        style={{ width: `${racerProgress}%` }}
                    >
                        {/* Animated shine effect */}
                        <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-30 animate-pulse"></div>
                    </div>

                    {/* Progress percentage badge */}
                    {racerProgress > 10 && (
                        <div
                            className="absolute top-1/2 transform -translate-y-1/2 bg-white text-gray-800 text-xs font-bold px-2 py-1 rounded-full shadow-lg"
                            style={{ right: `${Math.min(racerProgress, 85)}%` }}
                        >
                            {Math.round(racerProgress)}%
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
