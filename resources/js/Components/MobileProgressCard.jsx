import VisualIndicator from "./VisualIndicator";
import Badge from "./Badge";

export default function MobileProgressCard({
    topic,
    stats = {},
    color = "blue",
    onTopicClick = null,
}) {
    const getColorClasses = (color) => {
        const colors = {
            green: "bg-green-100 border-green-300 text-green-800",
            blue: "bg-blue-100 border-blue-300 text-blue-800",
            purple: "bg-purple-100 border-purple-300 text-purple-800",
            orange: "bg-orange-100 border-orange-300 text-orange-800",
        };
        return colors[color] || colors.green;
    };

    const getMasteryColor = (level) => {
        if (level >= 90) return "text-green-600 bg-green-100";
        if (level >= 80) return "text-blue-600 bg-blue-100";
        if (level >= 70) return "text-purple-600 bg-purple-100";
        if (level >= 60) return "text-yellow-600 bg-yellow-100";
        return "text-red-600 bg-red-100";
    };

    return (
        <div
            className={`border-2 rounded-xl p-4 hover-lift ${getColorClasses(
                color
            )} ${onTopicClick ? "cursor-pointer" : ""}`}
            onClick={onTopicClick}
        >
            {/* Header */}
            <div className="flex items-center justify-between mb-4">
                <div className="flex items-center">
                    <span className="text-2xl mr-2">{topic.icon}</span>
                    <h4 className="text-lg font-semibold">{topic.name}</h4>
                </div>
                <div
                    className={`px-2 py-1 rounded-full text-xs font-medium ${getMasteryColor(
                        stats.mastery_level || 0
                    )}`}
                >
                    {stats.mastery_category || "Not Started"}
                </div>
            </div>

            {/* Progress Indicator */}
            <div className="mb-4">
                <VisualIndicator
                    type="linear"
                    value={stats.mastery_level || 0}
                    maxValue={100}
                    color={color}
                    label="Mastery Progress"
                    size="small"
                />
            </div>

            {/* Stats Grid */}
            <div className="grid grid-cols-2 gap-3 text-sm mb-4">
                <div className="bg-white bg-opacity-50 rounded-lg p-2 text-center">
                    <div className="font-bold text-lg">
                        {stats.total_points || 0}
                    </div>
                    <div className="text-xs text-gray-600">Points</div>
                </div>
                <div className="bg-white bg-opacity-50 rounded-lg p-2 text-center">
                    <div className="font-bold text-lg">
                        {stats.total_quizzes || 0}
                    </div>
                    <div className="text-xs text-gray-600">Quizzes</div>
                </div>
                <div className="bg-white bg-opacity-50 rounded-lg p-2 text-center">
                    <div className="font-bold text-lg">
                        {stats.best_score || 0}
                    </div>
                    <div className="text-xs text-gray-600">Best Score</div>
                </div>
                <div className="bg-white bg-opacity-50 rounded-lg p-2 text-center">
                    <div className="font-bold text-lg">
                        {Math.round(stats.average_accuracy || 0)}%
                    </div>
                    <div className="text-xs text-gray-600">Accuracy</div>
                </div>
            </div>

            {/* Badges */}
            {stats.badges_earned && stats.badges_earned.length > 0 && (
                <div className="mb-3">
                    <div className="text-xs text-gray-600 mb-2">
                        Recent Badges:
                    </div>
                    <div className="flex flex-wrap gap-1">
                        {stats.badges_earned.slice(0, 3).map((badge, index) => (
                            <Badge
                                key={index}
                                type={badge.type}
                                earnedAt={badge.earned_at}
                                data={badge.data || {}}
                                size="small"
                                showTooltip={false}
                            />
                        ))}
                        {stats.badges_earned.length > 3 && (
                            <div className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300">
                                +{stats.badges_earned.length - 3}
                            </div>
                        )}
                    </div>
                </div>
            )}

            {/* Last Activity */}
            <div className="text-xs text-gray-500 pt-2 border-t border-white border-opacity-30">
                Last Activity: {stats.last_activity || "Never"}
            </div>
        </div>
    );
}
