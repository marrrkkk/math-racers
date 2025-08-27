import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import ProgressBar from "@/Components/ProgressBar";
import Badge from "@/Components/Badge";
import ProgressChart from "@/Components/ProgressChart";
import VisualIndicator from "@/Components/VisualIndicator";
import StatCard from "@/Components/StatCard";
import AchievementShowcase from "@/Components/AchievementShowcase";
import MobileProgressCard from "@/Components/MobileProgressCard";

export default function Progress({
    user,
    progressData = [],
    recentSessions = [],
    statistics = {},
    topicStats = {},
    allBadges = [],
}) {
    const topics = [
        { key: "addition", name: "Addition", icon: "➕", color: "green" },
        { key: "subtraction", name: "Subtraction", icon: "➖", color: "blue" },
        {
            key: "multiplication",
            name: "Multiplication",
            icon: "✖️",
            color: "purple",
        },
        { key: "division", name: "Division", icon: "➗", color: "orange" },
    ];

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

    const BadgeDisplay = ({ badges, limit = 6 }) => {
        const displayBadges = badges.slice(0, limit);
        const remainingCount = badges.length - limit;

        return (
            <div className="flex flex-wrap gap-2">
                {displayBadges.map((badge, index) => (
                    <Badge
                        key={index}
                        type={badge.type}
                        earnedAt={badge.earned_at}
                        data={badge.data || {}}
                        size="small"
                    />
                ))}
                {remainingCount > 0 && (
                    <div className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-300">
                        +{remainingCount} more
                    </div>
                )}
            </div>
        );
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        📊 My Progress - Grade {user.grade_level}
                    </h2>
                    <Link
                        href={route("student.leaderboard")}
                        className="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors duration-200"
                    >
                        🏆 View Leaderboard
                    </Link>
                </div>
            }
        >
            <Head title="My Progress" />

            <div className="py-6 sm:py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Overall Statistics */}
                    <div className="mb-8">
                        <h3 className="text-2xl font-bold text-gray-900 mb-4">
                            🎯 Overall Statistics
                        </h3>
                        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <StatCard
                                title="Total Points"
                                value={statistics.total_points || 0}
                                icon="🏆"
                                color="blue"
                                animated={true}
                            />
                            <StatCard
                                title="Badges Earned"
                                value={statistics.total_badges || 0}
                                icon="🏅"
                                color="yellow"
                                animated={true}
                            />
                            <StatCard
                                title="Quizzes Completed"
                                value={statistics.total_quizzes || 0}
                                icon="📝"
                                color="green"
                                animated={true}
                            />
                            <StatCard
                                title="Average Accuracy"
                                value={statistics.average_accuracy || 0}
                                suffix="%"
                                icon="🎯"
                                color="purple"
                                animated={true}
                            />
                        </div>

                        {/* Visual Progress Indicators */}
                        <div className="grid grid-cols-2 lg:grid-cols-4 gap-6">
                            <VisualIndicator
                                type="circular"
                                value={statistics.average_accuracy || 0}
                                maxValue={100}
                                color="blue"
                                label="Accuracy"
                                icon="🎯"
                                size="medium"
                            />
                            <VisualIndicator
                                type="gauge"
                                value={Math.min(
                                    (statistics.total_points || 0) / 10,
                                    100
                                )}
                                maxValue={100}
                                color="green"
                                label="Progress Level"
                                size="medium"
                            />
                            <VisualIndicator
                                type="steps"
                                value={statistics.total_badges || 0}
                                maxValue={20}
                                color="yellow"
                                label="Badge Collection"
                                size="medium"
                            />
                            <VisualIndicator
                                type="linear"
                                value={statistics.total_quizzes || 0}
                                maxValue={50}
                                color="purple"
                                label="Quiz Streak"
                                size="medium"
                            />
                        </div>
                    </div>

                    {/* Topic Progress */}
                    <div className="mb-8">
                        <h3 className="text-2xl font-bold text-gray-900 mb-4">
                            📚 Topic Progress
                        </h3>

                        {/* Mobile View */}
                        <div className="block sm:hidden space-y-4">
                            {topics.map((topic) => {
                                const stats = topicStats[topic.key] || {};
                                return (
                                    <MobileProgressCard
                                        key={topic.key}
                                        topic={topic}
                                        stats={stats}
                                        color={topic.color}
                                        onTopicClick={() => {
                                            // Could navigate to topic-specific page
                                            console.log(
                                                `Clicked on ${topic.name}`
                                            );
                                        }}
                                    />
                                );
                            })}
                        </div>

                        {/* Desktop View */}
                        <div className="hidden sm:grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {topics.map((topic) => {
                                const stats = topicStats[topic.key] || {};
                                return (
                                    <div
                                        key={topic.key}
                                        className={`border-2 rounded-xl p-6 hover-lift ${getColorClasses(
                                            topic.color
                                        )}`}
                                    >
                                        <div className="flex items-center justify-between mb-4">
                                            <div className="flex items-center">
                                                <span className="text-2xl mr-3">
                                                    {topic.icon}
                                                </span>
                                                <h4 className="text-lg font-semibold">
                                                    {topic.name}
                                                </h4>
                                            </div>
                                            <div
                                                className={`px-3 py-1 rounded-full text-sm font-medium ${getMasteryColor(
                                                    stats.mastery_level || 0
                                                )}`}
                                            >
                                                {stats.mastery_category ||
                                                    "Not Started"}
                                            </div>
                                        </div>

                                        <div className="space-y-4">
                                            <div>
                                                <div className="flex justify-between text-sm mb-2">
                                                    <span>Mastery Level</span>
                                                    <span className="font-medium">
                                                        {stats.mastery_level ||
                                                            0}
                                                        %
                                                    </span>
                                                </div>
                                                <ProgressBar
                                                    percentage={
                                                        stats.mastery_level || 0
                                                    }
                                                    color={topic.color}
                                                    animated={true}
                                                />
                                            </div>

                                            <div className="grid grid-cols-2 gap-4">
                                                <VisualIndicator
                                                    type="circular"
                                                    value={
                                                        stats.total_points || 0
                                                    }
                                                    maxValue={Math.max(
                                                        stats.total_points ||
                                                            100,
                                                        100
                                                    )}
                                                    color={topic.color}
                                                    label="Points"
                                                    size="small"
                                                />
                                                <VisualIndicator
                                                    type="linear"
                                                    value={Math.round(
                                                        stats.average_accuracy ||
                                                            0
                                                    )}
                                                    maxValue={100}
                                                    color={topic.color}
                                                    label="Accuracy"
                                                    size="small"
                                                />
                                            </div>

                                            <div className="grid grid-cols-2 gap-4 text-sm">
                                                <div className="bg-white bg-opacity-50 rounded-lg p-2 text-center">
                                                    <div className="font-bold text-lg">
                                                        {stats.total_quizzes ||
                                                            0}
                                                    </div>
                                                    <div className="text-xs text-gray-600">
                                                        Quizzes
                                                    </div>
                                                </div>
                                                <div className="bg-white bg-opacity-50 rounded-lg p-2 text-center">
                                                    <div className="font-bold text-lg">
                                                        {stats.best_score || 0}
                                                    </div>
                                                    <div className="text-xs text-gray-600">
                                                        Best Score
                                                    </div>
                                                </div>
                                            </div>

                                            {stats.badges_earned &&
                                                stats.badges_earned.length >
                                                    0 && (
                                                    <div>
                                                        <div className="text-sm text-gray-600 mb-2">
                                                            Recent Badges:
                                                        </div>
                                                        <BadgeDisplay
                                                            badges={
                                                                stats.badges_earned
                                                            }
                                                            limit={3}
                                                        />
                                                    </div>
                                                )}

                                            <div className="text-xs text-gray-500 pt-2 border-t border-white border-opacity-30">
                                                Last Activity:{" "}
                                                {stats.last_activity || "Never"}
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    {/* Progress Charts */}
                    <div className="mb-8">
                        <h3 className="text-2xl font-bold text-gray-900 mb-4">
                            📈 Performance Analytics
                        </h3>
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Topic Performance Chart */}
                            <ProgressChart
                                type="bar"
                                title="Points by Topic"
                                data={topics.map((topic) => ({
                                    label: topic.name,
                                    value:
                                        topicStats[topic.key]?.total_points ||
                                        0,
                                }))}
                                color="blue"
                                height={250}
                            />

                            {/* Mastery Level Chart */}
                            <ProgressChart
                                type="donut"
                                title="Mastery Distribution"
                                data={topics.map((topic) => ({
                                    label: topic.name,
                                    value:
                                        topicStats[topic.key]?.mastery_level ||
                                        0,
                                }))}
                                color="green"
                                height={250}
                            />
                        </div>

                        {/* Recent Performance Trend */}
                        {recentSessions && recentSessions.length > 0 && (
                            <div className="mt-6">
                                <ProgressChart
                                    type="line"
                                    title="Recent Quiz Performance"
                                    data={recentSessions
                                        .slice(0, 10)
                                        .reverse()
                                        .map((session, index) => ({
                                            label: `Quiz ${index + 1}`,
                                            value: session.points_earned || 0,
                                        }))}
                                    color="purple"
                                    height={200}
                                />
                            </div>
                        )}
                    </div>

                    {/* Achievement Showcase */}
                    <div className="mb-8">
                        <h3 className="text-2xl font-bold text-gray-900 mb-4">
                            🏆 Achievement Gallery
                        </h3>
                        <AchievementShowcase
                            badges={allBadges || []}
                            title="All Earned Badges"
                            showProgress={true}
                            progressData={{
                                totalPossible: 20,
                                completionRate: Math.min(
                                    ((allBadges?.length || 0) / 20) * 100,
                                    100
                                ),
                            }}
                            maxDisplay={12}
                            layout="grid"
                        />
                    </div>

                    {/* Recent Activity */}
                    <div className="mb-8">
                        <h3 className="text-2xl font-bold text-gray-900 mb-4">
                            📈 Recent Quiz Sessions
                        </h3>
                        <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                            {recentSessions && recentSessions.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Topic
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Score
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Accuracy
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {recentSessions.map(
                                                (session, index) => (
                                                    <tr
                                                        key={index}
                                                        className="hover:bg-gray-50"
                                                    >
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="flex items-center">
                                                                <span className="mr-2">
                                                                    {topics.find(
                                                                        (t) =>
                                                                            t.key ===
                                                                            session.question_type
                                                                    )?.icon ||
                                                                        "📝"}
                                                                </span>
                                                                <span className="text-sm font-medium text-gray-900 capitalize">
                                                                    {
                                                                        session.question_type
                                                                    }
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className="text-sm font-bold text-blue-600">
                                                                {
                                                                    session.points_earned
                                                                }{" "}
                                                                pts
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span
                                                                className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                                    session.accuracy >=
                                                                    80
                                                                        ? "bg-green-100 text-green-800"
                                                                        : session.accuracy >=
                                                                          60
                                                                        ? "bg-yellow-100 text-yellow-800"
                                                                        : "bg-red-100 text-red-800"
                                                                }`}
                                                            >
                                                                {
                                                                    session.accuracy
                                                                }
                                                                %
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {new Date(
                                                                session.completed_at
                                                            ).toLocaleDateString()}
                                                        </td>
                                                    </tr>
                                                )
                                            )}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <div className="text-6xl mb-4">🎯</div>
                                    <h4 className="text-lg font-medium text-gray-900 mb-2">
                                        No Quiz Sessions Yet
                                    </h4>
                                    <p className="text-gray-500 mb-4">
                                        Complete some quizzes to see your
                                        progress here!
                                    </p>
                                    <Link
                                        href={route(
                                            "student.topics",
                                            user.grade_level
                                        )}
                                        className="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200"
                                    >
                                        Start Learning 🚀
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Action Buttons */}
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            href={route("student.topics", user.grade_level)}
                            className="inline-flex items-center justify-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200"
                        >
                            🎯 Practice More
                        </Link>
                        <Link
                            href={route("student.leaderboard")}
                            className="inline-flex items-center justify-center px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors duration-200"
                        >
                            🏆 View Leaderboard
                        </Link>
                        <Link
                            href={route("student.dashboard")}
                            className="inline-flex items-center justify-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-colors duration-200"
                        >
                            🏠 Back to Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
