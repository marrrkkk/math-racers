import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";
import VisualIndicator from "@/Components/VisualIndicator";
import StatCard from "@/Components/StatCard";

export default function Leaderboard({
    user,
    leaderboard = [],
    currentUserRank = null,
    filters = {},
    availableGrades = [1, 2, 3],
    availableTopics = {},
}) {
    const [selectedGrade, setSelectedGrade] = useState(
        filters.grade || user?.grade_level || 1
    );
    const [selectedTopic, setSelectedTopic] = useState(filters.topic || "all");

    const handleFilterChange = (grade, topic) => {
        router.get(
            route("student.leaderboard"),
            {
                grade: grade,
                topic: topic,
            },
            {
                preserveState: true,
                preserveScroll: true,
            }
        );
    };

    const getRankIcon = (rank) => {
        switch (rank) {
            case 1:
                return "🥇";
            case 2:
                return "🥈";
            case 3:
                return "🥉";
            default:
                return "🏁";
        }
    };

    const getRankColor = (rank) => {
        switch (rank) {
            case 1:
                return "bg-gradient-to-r from-yellow-400 to-yellow-500 text-white";
            case 2:
                return "bg-gradient-to-r from-gray-300 to-gray-400 text-gray-800";
            case 3:
                return "bg-gradient-to-r from-orange-400 to-orange-500 text-white";
            default:
                return "bg-white border border-gray-200";
        }
    };

    const getTopicIcon = (topic) => {
        const icons = {
            addition: "➕",
            subtraction: "➖",
            multiplication: "✖️",
            division: "➗",
            all: "🏆",
        };
        return icons[topic] || "📝";
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        🏆 Leaderboard - Grade {filters.grade}
                    </h2>
                    <Link
                        href={route("student.progress")}
                        className="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200"
                    >
                        📊 My Progress
                    </Link>
                </div>
            }
        >
            <Head title="Leaderboard" />

            <div className="py-6 sm:py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Filters */}
                    <div className="mb-8">
                        <div className="bg-white border border-gray-200 rounded-xl p-6">
                            <h3 className="text-lg font-semibold text-gray-900 mb-4">
                                🎯 Filter Rankings
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Grade Filter */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Grade Level
                                    </label>
                                    <div className="grid grid-cols-3 gap-2">
                                        {availableGrades.map((grade) => (
                                            <button
                                                key={grade}
                                                onClick={() => {
                                                    setSelectedGrade(grade);
                                                    handleFilterChange(
                                                        grade,
                                                        selectedTopic
                                                    );
                                                }}
                                                className={`px-4 py-2 rounded-lg font-medium transition-colors duration-200 ${
                                                    selectedGrade === grade
                                                        ? "bg-blue-500 text-white"
                                                        : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                                                }`}
                                            >
                                                Grade {grade}
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                {/* Topic Filter */}
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Math Topic
                                    </label>
                                    <div className="grid grid-cols-2 gap-2">
                                        {Object.entries(availableTopics).map(
                                            ([key, name]) => (
                                                <button
                                                    key={key}
                                                    onClick={() => {
                                                        setSelectedTopic(key);
                                                        handleFilterChange(
                                                            selectedGrade,
                                                            key
                                                        );
                                                    }}
                                                    className={`px-3 py-2 rounded-lg font-medium transition-colors duration-200 text-sm ${
                                                        selectedTopic === key
                                                            ? "bg-yellow-500 text-white"
                                                            : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                                                    }`}
                                                >
                                                    <span className="mr-1">
                                                        {getTopicIcon(key)}
                                                    </span>
                                                    {name}
                                                </button>
                                            )
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Leaderboard Summary Stats */}
                    <div className="mb-8">
                        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <StatCard
                                title="Your Rank"
                                value={
                                    currentUserRank ||
                                    leaderboard.find((s) => s.is_current_user)
                                        ?.rank ||
                                    "N/A"
                                }
                                prefix="#"
                                icon="🏁"
                                color="blue"
                                size="small"
                            />
                            <StatCard
                                title="Total Racers"
                                value={leaderboard.length}
                                icon="👥"
                                color="green"
                                size="small"
                            />
                            <StatCard
                                title="Top Score"
                                value={leaderboard[0]?.points || 0}
                                icon="🏆"
                                color="yellow"
                                size="small"
                            />
                            <StatCard
                                title="Your Points"
                                value={
                                    leaderboard.find((s) => s.is_current_user)
                                        ?.points || 0
                                }
                                icon="⭐"
                                color="purple"
                                size="small"
                            />
                        </div>
                    </div>

                    {/* Current User Rank (if not in top 50) */}
                    {currentUserRank && currentUserRank > 50 && (
                        <div className="mb-6">
                            <div className="bg-gradient-to-r from-blue-50 to-purple-50 border border-blue-200 rounded-xl p-6">
                                <div className="flex flex-col lg:flex-row items-center justify-between">
                                    <div className="flex items-center mb-4 lg:mb-0">
                                        <span className="text-3xl mr-4">
                                            🎯
                                        </span>
                                        <div>
                                            <div className="font-semibold text-gray-900 text-lg">
                                                Your Current Rank
                                            </div>
                                            <div className="text-sm text-gray-600">
                                                Keep practicing to climb higher!
                                            </div>
                                        </div>
                                    </div>
                                    <div className="flex items-center space-x-6">
                                        <VisualIndicator
                                            type="circular"
                                            value={Math.max(
                                                100 - currentUserRank,
                                                0
                                            )}
                                            maxValue={100}
                                            color="blue"
                                            label="Progress"
                                            size="small"
                                        />
                                        <div className="text-center">
                                            <div className="text-3xl font-bold text-blue-600">
                                                #{currentUserRank}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                Current Rank
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Leaderboard */}
                    <div className="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <div className="bg-gradient-to-r from-yellow-400 to-orange-500 px-6 py-4">
                            <h3 className="text-xl font-bold text-white flex items-center">
                                <span className="mr-2">🏆</span>
                                {filters.topic === "all"
                                    ? "Overall Rankings"
                                    : `${
                                          availableTopics[filters.topic]
                                      } Rankings`}
                                <span className="ml-2 text-yellow-100">
                                    - Grade {filters.grade}
                                </span>
                            </h3>
                        </div>

                        {leaderboard && leaderboard.length > 0 ? (
                            <div className="divide-y divide-gray-200">
                                {leaderboard.map((student, index) => (
                                    <div
                                        key={index}
                                        className={`px-4 sm:px-6 py-4 transition-colors duration-200 ${
                                            student.is_current_user
                                                ? "bg-blue-50 border-l-4 border-blue-500"
                                                : "hover:bg-gray-50"
                                        }`}
                                    >
                                        {/* Mobile Layout */}
                                        <div className="flex flex-col sm:hidden space-y-3">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center">
                                                    <div
                                                        className={`flex items-center justify-center w-10 h-10 rounded-full mr-3 font-bold text-sm ${getRankColor(
                                                            student.rank
                                                        )}`}
                                                    >
                                                        <span>
                                                            {getRankIcon(
                                                                student.rank
                                                            )}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span
                                                            className={`font-semibold ${
                                                                student.is_current_user
                                                                    ? "text-blue-700"
                                                                    : "text-gray-900"
                                                            }`}
                                                        >
                                                            {student.name}
                                                        </span>
                                                        {student.is_current_user && (
                                                            <span className="ml-2 text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                                                                You
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-xl font-bold text-yellow-600">
                                                        {student.points}
                                                    </div>
                                                    <div className="text-xs text-gray-500">
                                                        points
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="flex items-center justify-between text-sm text-gray-500">
                                                <span>
                                                    🏁 Rank #{student.rank}
                                                </span>
                                                {student.badge_count > 0 && (
                                                    <span>
                                                        🏆 {student.badge_count}{" "}
                                                        badges
                                                    </span>
                                                )}
                                                {student.mastery_level !==
                                                    undefined && (
                                                    <span>
                                                        {student.mastery_level}%
                                                        mastery
                                                    </span>
                                                )}
                                            </div>
                                        </div>

                                        {/* Desktop Layout */}
                                        <div className="hidden sm:flex items-center justify-between">
                                            <div className="flex items-center flex-1">
                                                {/* Rank */}
                                                <div
                                                    className={`flex items-center justify-center w-12 h-12 rounded-full mr-4 font-bold ${getRankColor(
                                                        student.rank
                                                    )}`}
                                                >
                                                    <span className="text-lg">
                                                        {getRankIcon(
                                                            student.rank
                                                        )}
                                                    </span>
                                                </div>

                                                {/* Student Info */}
                                                <div className="flex-1">
                                                    <div className="flex items-center">
                                                        <span
                                                            className={`font-semibold text-lg ${
                                                                student.is_current_user
                                                                    ? "text-blue-700"
                                                                    : "text-gray-900"
                                                            }`}
                                                        >
                                                            {student.name}
                                                            {student.is_current_user && (
                                                                <span className="ml-2 text-sm bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                                                                    You
                                                                </span>
                                                            )}
                                                        </span>
                                                    </div>
                                                    <div className="text-sm text-gray-500 flex items-center mt-1">
                                                        <span className="mr-4">
                                                            🏁 Rank #
                                                            {student.rank}
                                                        </span>
                                                        {student.badge_count >
                                                            0 && (
                                                            <span className="mr-4">
                                                                🏆{" "}
                                                                {
                                                                    student.badge_count
                                                                }{" "}
                                                                badges
                                                            </span>
                                                        )}
                                                        {student.mastery_level !==
                                                            undefined && (
                                                            <span>
                                                                📊{" "}
                                                                {
                                                                    student.mastery_level
                                                                }
                                                                % mastery
                                                            </span>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Points and Visual Indicator */}
                                            <div className="flex items-center space-x-4">
                                                <VisualIndicator
                                                    type="circular"
                                                    value={
                                                        student.mastery_level ||
                                                        0
                                                    }
                                                    maxValue={100}
                                                    color="green"
                                                    size="small"
                                                    showValue={false}
                                                />
                                                <div className="text-right">
                                                    <div className="text-2xl font-bold text-yellow-600 mb-1">
                                                        {student.points}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        points
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-12">
                                <div className="text-6xl mb-4">🏁</div>
                                <h4 className="text-lg font-medium text-gray-900 mb-2">
                                    No Rankings Yet
                                </h4>
                                <p className="text-gray-500 mb-4">
                                    Be the first to complete quizzes and appear
                                    on the leaderboard!
                                </p>
                                <Link
                                    href={route(
                                        "student.topics",
                                        user.grade_level
                                    )}
                                    className="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200"
                                >
                                    Start Racing 🚀
                                </Link>
                            </div>
                        )}
                    </div>

                    {/* Motivational Section */}
                    <div className="mt-8 bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-xl p-6">
                        <div className="text-center">
                            <h4 className="text-lg font-semibold text-gray-900 mb-2">
                                🌟 Keep Racing to the Top!
                            </h4>
                            <p className="text-gray-600 mb-4">
                                Complete more quizzes, improve your accuracy,
                                and earn badges to climb the leaderboard!
                            </p>
                            <div className="flex flex-col sm:flex-row gap-3 justify-center">
                                <Link
                                    href={route(
                                        "student.topics",
                                        user.grade_level
                                    )}
                                    className="inline-flex items-center justify-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
                                >
                                    🎯 Practice Now
                                </Link>
                                <Link
                                    href={route("student.progress")}
                                    className="inline-flex items-center justify-center px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200"
                                >
                                    📊 View My Progress
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
