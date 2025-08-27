import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import {
    ArrowLeftIcon,
    TrophyIcon,
    ClockIcon,
    AcademicCapIcon,
    ChartBarIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
} from "@heroicons/react/24/outline";

export default function StudentDetail({
    student,
    quizzesByTopic,
    progressByTopic,
    competencyAnalysis,
    activityTimeline,
}) {
    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    };

    const getPerformanceColor = (accuracy) => {
        if (accuracy >= 80) return "text-green-600 bg-green-100";
        if (accuracy >= 60) return "text-yellow-600 bg-yellow-100";
        return "text-red-600 bg-red-100";
    };

    const getMasteryColor = (level) => {
        if (level >= 90) return "text-purple-600 bg-purple-100";
        if (level >= 80) return "text-green-600 bg-green-100";
        if (level >= 70) return "text-blue-600 bg-blue-100";
        if (level >= 60) return "text-yellow-600 bg-yellow-100";
        return "text-red-600 bg-red-100";
    };

    const getTrendIcon = (trend) => {
        switch (trend) {
            case "improving":
                return <CheckCircleIcon className="h-5 w-5 text-green-500" />;
            case "declining":
                return (
                    <ExclamationTriangleIcon className="h-5 w-5 text-red-500" />
                );
            default:
                return <ChartBarIcon className="h-5 w-5 text-gray-500" />;
        }
    };

    const topics = ["addition", "subtraction", "multiplication", "division"];

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <Link
                            href={route("teacher.student-performance")}
                            className="text-gray-600 hover:text-gray-800"
                        >
                            <ArrowLeftIcon className="h-6 w-6" />
                        </Link>
                        <div>
                            <h2 className="text-xl font-semibold leading-tight text-gray-800">
                                {student.name} - Grade {student.grade_level}
                            </h2>
                            <p className="text-sm text-gray-600">
                                Detailed Performance Analytics
                            </p>
                        </div>
                    </div>
                </div>
            }
        >
            <Head title={`${student.name} - Student Details`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Student Overview */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <TrophyIcon className="h-8 w-8 text-yellow-500" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Total Points
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {student.total_points}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <AcademicCapIcon className="h-8 w-8 text-blue-500" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Total Badges
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {student.total_badges}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <ChartBarIcon className="h-8 w-8 text-green-500" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Avg Accuracy
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {Object.values(quizzesByTopic).length >
                                        0
                                            ? (
                                                  Object.values(
                                                      quizzesByTopic
                                                  ).reduce(
                                                      (sum, topic) =>
                                                          sum +
                                                          topic.average_accuracy,
                                                      0
                                                  ) /
                                                  Object.values(quizzesByTopic)
                                                      .length
                                              ).toFixed(1)
                                            : 0}
                                        %
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <ClockIcon className="h-8 w-8 text-purple-500" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Total Quizzes
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {Object.values(quizzesByTopic).reduce(
                                            (sum, topic) =>
                                                sum + topic.total_attempts,
                                            0
                                        )}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Topic Performance */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-6">
                                Topic Performance Overview
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {topics.map((topic) => {
                                    const quizData = quizzesByTopic[topic];
                                    const progressData = progressByTopic[topic];

                                    if (!quizData && !progressData) return null;

                                    return (
                                        <div
                                            key={topic}
                                            className="border rounded-lg p-6"
                                        >
                                            <div className="flex items-center justify-between mb-4">
                                                <h4 className="text-lg font-medium text-gray-900 capitalize">
                                                    {topic}
                                                </h4>
                                                {progressData && (
                                                    <span
                                                        className={`inline-flex px-3 py-1 text-sm font-semibold rounded-full ${getMasteryColor(
                                                            progressData.mastery_level
                                                        )}`}
                                                    >
                                                        {
                                                            progressData.mastery_category
                                                        }
                                                    </span>
                                                )}
                                            </div>

                                            <div className="grid grid-cols-2 gap-4 mb-4">
                                                <div>
                                                    <p className="text-sm text-gray-600">
                                                        Total Points
                                                    </p>
                                                    <p className="text-xl font-bold text-gray-900">
                                                        {progressData?.total_points ||
                                                            0}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-gray-600">
                                                        Mastery Level
                                                    </p>
                                                    <p className="text-xl font-bold text-gray-900">
                                                        {progressData?.mastery_level?.toFixed(
                                                            1
                                                        ) || 0}
                                                        %
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-gray-600">
                                                        Quiz Attempts
                                                    </p>
                                                    <p className="text-xl font-bold text-gray-900">
                                                        {quizData?.total_attempts ||
                                                            0}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p className="text-sm text-gray-600">
                                                        Avg Accuracy
                                                    </p>
                                                    <p className="text-xl font-bold text-gray-900">
                                                        {quizData?.average_accuracy?.toFixed(
                                                            1
                                                        ) || 0}
                                                        %
                                                    </p>
                                                </div>
                                            </div>

                                            {progressData?.badges_earned &&
                                                progressData.badges_earned
                                                    .length > 0 && (
                                                    <div>
                                                        <p className="text-sm text-gray-600 mb-2">
                                                            Badges Earned
                                                        </p>
                                                        <div className="flex flex-wrap gap-2">
                                                            {progressData.badges_earned.map(
                                                                (
                                                                    badge,
                                                                    index
                                                                ) => (
                                                                    <span
                                                                        key={
                                                                            index
                                                                        }
                                                                        className="inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full"
                                                                    >
                                                                        <TrophyIcon className="h-3 w-3 mr-1" />
                                                                        {
                                                                            badge.type
                                                                        }
                                                                    </span>
                                                                )
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>

                    {/* Competency Analysis */}
                    {Object.keys(competencyAnalysis).length > 0 && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-6">
                                    Competency Analysis & Recommendations
                                </h3>
                                <div className="space-y-6">
                                    {Object.entries(competencyAnalysis).map(
                                        ([topic, analysis]) => (
                                            <div
                                                key={topic}
                                                className="border rounded-lg p-6"
                                            >
                                                <div className="flex items-center justify-between mb-4">
                                                    <h4 className="text-lg font-medium text-gray-900 capitalize">
                                                        {topic}
                                                    </h4>
                                                    <div className="flex items-center space-x-2">
                                                        {getTrendIcon(
                                                            analysis.improvement_trend
                                                        )}
                                                        <span className="text-sm text-gray-600 capitalize">
                                                            {
                                                                analysis.improvement_trend
                                                            }
                                                        </span>
                                                    </div>
                                                </div>

                                                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                    <div>
                                                        <p className="text-sm text-gray-600">
                                                            Overall Accuracy
                                                        </p>
                                                        <p className="text-lg font-bold text-gray-900">
                                                            {
                                                                analysis.overall_accuracy
                                                            }
                                                            %
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-sm text-gray-600">
                                                            Recent Accuracy
                                                        </p>
                                                        <p className="text-lg font-bold text-gray-900">
                                                            {
                                                                analysis.recent_accuracy
                                                            }
                                                            %
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-sm text-gray-600">
                                                            Total Attempts
                                                        </p>
                                                        <p className="text-lg font-bold text-gray-900">
                                                            {
                                                                analysis.total_attempts
                                                            }
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-sm text-gray-600">
                                                            Mastery Level
                                                        </p>
                                                        <span
                                                            className={`inline-flex px-2 py-1 text-sm font-semibold rounded-full ${getMasteryColor(
                                                                analysis.overall_accuracy
                                                            )}`}
                                                        >
                                                            {
                                                                analysis.mastery_level
                                                            }
                                                        </span>
                                                    </div>
                                                </div>

                                                {analysis.recommendations &&
                                                    analysis.recommendations
                                                        .length > 0 && (
                                                        <div>
                                                            <p className="text-sm font-medium text-gray-700 mb-2">
                                                                Recommendations:
                                                            </p>
                                                            <ul className="list-disc list-inside space-y-1">
                                                                {analysis.recommendations.map(
                                                                    (
                                                                        rec,
                                                                        index
                                                                    ) => (
                                                                        <li
                                                                            key={
                                                                                index
                                                                            }
                                                                            className="text-sm text-gray-600"
                                                                        >
                                                                            {
                                                                                rec
                                                                            }
                                                                        </li>
                                                                    )
                                                                )}
                                                            </ul>
                                                        </div>
                                                    )}
                                            </div>
                                        )
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Recent Activity Timeline */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-6">
                                Recent Activity Timeline
                            </h3>
                            {activityTimeline.length > 0 ? (
                                <div className="flow-root">
                                    <ul className="-mb-8">
                                        {activityTimeline.map(
                                            (activity, index) => (
                                                <li key={index}>
                                                    <div className="relative pb-8">
                                                        {index !==
                                                            activityTimeline.length -
                                                                1 && (
                                                            <span className="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" />
                                                        )}
                                                        <div className="relative flex space-x-3">
                                                            <div>
                                                                <span
                                                                    className={`h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white ${getPerformanceColor(
                                                                        activity.accuracy
                                                                    )
                                                                        .replace(
                                                                            "text-",
                                                                            "bg-"
                                                                        )
                                                                        .replace(
                                                                            "-600",
                                                                            "-500"
                                                                        )}`}
                                                                >
                                                                    <AcademicCapIcon className="h-5 w-5 text-white" />
                                                                </span>
                                                            </div>
                                                            <div className="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                                <div>
                                                                    <p className="text-sm text-gray-500">
                                                                        Completed{" "}
                                                                        <span className="font-medium text-gray-900 capitalize">
                                                                            {
                                                                                activity.topic
                                                                            }
                                                                        </span>{" "}
                                                                        quiz
                                                                        <span className="whitespace-nowrap">
                                                                            {" "}
                                                                            for
                                                                            Grade{" "}
                                                                            {
                                                                                activity.grade
                                                                            }
                                                                        </span>
                                                                    </p>
                                                                    <div className="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                                                        <span>
                                                                            Score:{" "}
                                                                            {
                                                                                activity.score
                                                                            }{" "}
                                                                            pts
                                                                        </span>
                                                                        <span>
                                                                            Accuracy:{" "}
                                                                            {activity.accuracy.toFixed(
                                                                                1
                                                                            )}
                                                                            %
                                                                        </span>
                                                                        <span>
                                                                            Questions:{" "}
                                                                            {
                                                                                activity.questions
                                                                            }
                                                                        </span>
                                                                        <span>
                                                                            Time:{" "}
                                                                            {Math.floor(
                                                                                activity.time_taken /
                                                                                    60
                                                                            )}
                                                                            :
                                                                            {(
                                                                                activity.time_taken %
                                                                                60
                                                                            )
                                                                                .toString()
                                                                                .padStart(
                                                                                    2,
                                                                                    "0"
                                                                                )}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div className="text-right text-sm whitespace-nowrap text-gray-500">
                                                                    {formatDate(
                                                                        activity.date
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            )
                                        )}
                                    </ul>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <ClockIcon className="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">
                                        No recent activity
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        This student hasn't completed any
                                        quizzes recently.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
