import Badge from "./Badge";
import VisualIndicator from "./VisualIndicator";

export default function AchievementShowcase({
    badges = [],
    title = "Achievements",
    showProgress = false,
    progressData = null,
    maxDisplay = 8,
    layout = "grid", // 'grid' or 'carousel'
}) {
    const displayBadges = badges.slice(0, maxDisplay);
    const remainingCount = badges.length - maxDisplay;

    if (layout === "carousel") {
        return (
            <div className="bg-white border border-gray-200 rounded-xl p-6">
                <h4 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span className="mr-2">🏆</span>
                    {title}
                    {badges.length > 0 && (
                        <span className="ml-2 text-sm bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                            {badges.length}
                        </span>
                    )}
                </h4>

                {badges.length > 0 ? (
                    <div className="relative">
                        <div className="flex overflow-x-auto pb-4 space-x-3 scrollbar-hide">
                            {displayBadges.map((badge, index) => (
                                <div key={index} className="flex-shrink-0">
                                    <div className="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-3 text-center min-w-[120px]">
                                        <Badge
                                            type={badge.type}
                                            earnedAt={badge.earned_at}
                                            data={badge.data || {}}
                                            size="small"
                                        />
                                        {badge.data?.description && (
                                            <p className="text-xs text-gray-600 mt-2">
                                                {badge.data.description}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            ))}
                            {remainingCount > 0 && (
                                <div className="flex-shrink-0">
                                    <div className="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center min-w-[120px] flex items-center justify-center">
                                        <div className="text-gray-500">
                                            <div className="text-lg font-bold">
                                                +{remainingCount}
                                            </div>
                                            <div className="text-xs">more</div>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Progress indicators if enabled */}
                        {showProgress && progressData && (
                            <div className="mt-4 grid grid-cols-2 gap-4">
                                <VisualIndicator
                                    type="linear"
                                    value={badges.length}
                                    maxValue={progressData.totalPossible || 20}
                                    color="yellow"
                                    label="Badge Collection"
                                    size="small"
                                />
                                <VisualIndicator
                                    type="circular"
                                    value={progressData.completionRate || 0}
                                    maxValue={100}
                                    color="green"
                                    label="Achievement Rate"
                                    size="small"
                                />
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="text-center py-8">
                        <div className="text-4xl mb-3">🎯</div>
                        <h4 className="text-lg font-medium text-gray-900 mb-2">
                            No Achievements Yet
                        </h4>
                        <p className="text-gray-500 text-sm">
                            Complete quizzes and improve your skills to earn
                            badges!
                        </p>
                    </div>
                )}
            </div>
        );
    }

    // Default grid layout
    return (
        <div className="bg-white border border-gray-200 rounded-xl p-6">
            <h4 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span className="mr-2">🏆</span>
                {title}
                {badges.length > 0 && (
                    <span className="ml-2 text-sm bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                        {badges.length}
                    </span>
                )}
            </h4>

            {badges.length > 0 ? (
                <div>
                    <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        {displayBadges.map((badge, index) => (
                            <div
                                key={index}
                                className="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-3 text-center"
                            >
                                <Badge
                                    type={badge.type}
                                    earnedAt={badge.earned_at}
                                    data={badge.data || {}}
                                    size="small"
                                />
                                {badge.data?.description && (
                                    <p className="text-xs text-gray-600 mt-2">
                                        {badge.data.description}
                                    </p>
                                )}
                                <div className="text-xs text-gray-500 mt-1">
                                    {new Date(
                                        badge.earned_at
                                    ).toLocaleDateString()}
                                </div>
                            </div>
                        ))}
                        {remainingCount > 0 && (
                            <div className="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center flex items-center justify-center">
                                <div className="text-gray-500">
                                    <div className="text-lg font-bold">
                                        +{remainingCount}
                                    </div>
                                    <div className="text-xs">more badges</div>
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Progress indicators if enabled */}
                    {showProgress && progressData && (
                        <div className="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <VisualIndicator
                                type="linear"
                                value={badges.length}
                                maxValue={progressData.totalPossible || 20}
                                color="yellow"
                                label="Badge Collection Progress"
                                size="small"
                            />
                            <VisualIndicator
                                type="steps"
                                value={progressData.completionRate || 0}
                                maxValue={100}
                                color="green"
                                label="Achievement Completion"
                                size="small"
                            />
                        </div>
                    )}
                </div>
            ) : (
                <div className="text-center py-8">
                    <div className="text-4xl mb-3">🎯</div>
                    <h4 className="text-lg font-medium text-gray-900 mb-2">
                        No Achievements Yet
                    </h4>
                    <p className="text-gray-500 text-sm mb-4">
                        Complete quizzes and improve your skills to earn badges!
                    </p>
                    {showProgress && progressData && (
                        <div className="max-w-xs mx-auto">
                            <VisualIndicator
                                type="linear"
                                value={0}
                                maxValue={progressData.totalPossible || 20}
                                color="gray"
                                label="Ready to start earning badges?"
                                size="small"
                            />
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}
