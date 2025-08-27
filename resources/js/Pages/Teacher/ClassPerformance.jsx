import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";
import {
    ChartBarIcon,
    AcademicCapIcon,
    ExclamationTriangleIcon,
    TrophyIcon,
    UserGroupIcon,
    ClockIcon,
} from "@heroicons/react/24/outline";

export default function ClassPerformance({
    classStats,
    competencyMastery,
    topicTrends,
    strugglingStudents,
    topPerformers,
    filters,
    availableGrades,
    availableTimePeriods,
}) {
    const handleFilterChange = (key, value) => {
        const newFilters = { ...filters, [key]: value };
        router.get(route("teacher.class-performance"), newFilters, {
            preserveState: true,
            replace: true,
        });
    };

    const getMasteryColor = (level) => {
        if (level >= 90) return "bg-purple-500";
        if (level >= 80) return "bg-green-500";
        if (level >= 70) return "bg-blue-500";
        if (level >= 60) return "bg-yellow-500";
        if (level >= 50) return "bg-orange-500";
        return "bg-red-500";
    };

    const getMasteryLabel = (key) => {
        const labels = {
            expert: "Expert (90%+)",
            advanced: "Advanced (80-89%)",
            proficient: "Proficient (70-79%)",
            developing: "Developing (60-69%)",
            beginning: "Beginning (50-59%)",
            needs_support: "Needs Support (<50%)",
        };
        return labels[key] || key;
    };

    const getPerformanceColor = (accuracy) => {
        if (accuracy >= 80) return "text-green-600";
        if (accuracy >= 60) return "text-yellow-600";
        return "text-red-600";
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Class Performance Analytics
                    </h2>
                    <Link
                        href={route("teacher.dashboard")}
                        className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                    >
                        Back to Dashboard
                    </Link>
                </div>
            }
        >
            <Head title="Class Performance" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Filters */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Grade Level
                                    </label>
                                    <select
                                        value={filters.grade}
                                        onChange={(e) =>
                                            handleFilterChange(
                                                "grade",
                                                e.target.value
                                            )
                                        }
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        {availableGrades.map((grade) => (
                                            <option key={grade} value={grade}>
                                                Grade {grade}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Time Period
                                    </label>
                                    <select
                                        value={filters.time_period}
                                        onChange={(e) =>
                                            handleFilterChange(
                                                "time_period",
                                                e.target.value
                                            )
                                        }
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    >
                                        {Object.entries(
                                            availableTimePeriods
                                        ).map(([value, label]) => (
                                            <option key={value} value={value}>
                                                {label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex items-end">
                                    <Link
                                        href={route(
                                            "teacher.topic-assignments",
                                            { grade: filters.grade }
                                        )}
                                        className="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center"
                                    >
                                        Assign Topics
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Class Overview Statistics */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <UserGroupIcon className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Total Students
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {classStats.total_students}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <ChartBarIcon className="h-8 w-8 text-green-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Active Students
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {classStats.active_students}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <AcademicCapIcon className="h-8 w-8 text-purple-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Avg Accuracy
                                    </p>
                                    <p
                                        className={`text-2xl font-bold ${getPerformanceColor(
                                            classStats.average_accuracy
                                        )}`}
                                    >
                                        {classStats.average_accuracy}%
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <ClockIcon className="h-8 w-8 text-orange-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Completion Rate
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {classStats.completion_rate}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Competency Mastery */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-6">
                                Competency Mastery by Topic
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {Object.entries(competencyMastery).map(
                                    ([topic, mastery]) => (
                                        <div
                                            key={topic}
                                            className="border rounded-lg p-6"
                                        >
                                            <div className="flex items-center justify-between mb-4">
                                                <h4 className="text-lg font-medium text-gray-900 capitalize">
                                                    {topic}
                                                </h4>
                                                <span className="text-sm text-gray-600">
                                                    {mastery.students_attempted}
                                                    /{mastery.total_students}{" "}
                                                    students
                                                </span>
                                            </div>

                                            <div className="mb-4">
                                                <div className="flex justify-between text-sm text-gray-600 mb-2">
                                                    <span>Average Mastery</span>
                                                    <span>
                                                        {
                                                            mastery.average_mastery
                                                        }
                                                        %
                                                    </span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-2">
                                                    <div
                                                        className={`h-2 rounded-full ${getMasteryColor(
                                                            mastery.average_mastery
                                                        )}`}
                                                        style={{
                                                            width: `${mastery.average_mastery}%`,
                                                        }}
                                                    ></div>
                                                </div>
                                            </div>

                                            <div className="space-y-2">
                                                {Object.entries(
                                                    mastery.mastery_levels
                                                ).map(([level, count]) => (
                                                    <div
                                                        key={level}
                                                        className="flex items-center justify-between text-sm"
                                                    >
                                                        <div className="flex items-center">
                                                            <div
                                                                className={`w-3 h-3 rounded-full ${getMasteryColor(
                                                                    level ===
                                                                        "expert"
                                                                        ? 95
                                                                        : level ===
                                                                          "advanced"
                                                                        ? 85
                                                                        : level ===
                                                                          "proficient"
                                                                        ? 75
                                                                        : level ===
                                                                          "developing"
                                                                        ? 65
                                                                        : level ===
                                                                          "beginning"
                                                                        ? 55
                                                                        : 45
                                                                )} mr-2`}
                                                            ></div>
                                                            <span className="text-gray-700">
                                                                {getMasteryLabel(
                                                                    level
                                                                )}
                                                            </span>
                                                        </div>
                                                        <span className="font-medium text-gray-900">
                                                            {count}
                                                        </span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Struggling Students */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-medium text-gray-900">
                                        Students Needing Support
                                    </h3>
                                    <ExclamationTriangleIcon className="h-6 w-6 text-red-500" />
                                </div>
                                {strugglingStudents.length > 0 ? (
                                    <div className="space-y-4">
                                        {strugglingStudents.map((student) => (
                                            <div
                                                key={student.id}
                                                className="border rounded-lg p-4"
                                            >
                                                <div className="flex items-center justify-between mb-2">
                                                    <Link
                                                        href={route(
                                                            "teacher.student.detail",
                                                            student.id
                                                        )}
                                                        className="font-medium text-blue-600 hover:text-blue-800"
                                                    >
                                                        {student.name}
                                                    </Link>
                                                    <span className="text-sm text-red-600 font-medium">
                                                        {
                                                            student.average_accuracy
                                                        }
                                                        % accuracy
                                                    </span>
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    <p>
                                                        Quizzes taken:{" "}
                                                        {student.quiz_count}
                                                    </p>
                                                    {student.weakest_topic && (
                                                        <p>
                                                            Weakest topic:{" "}
                                                            <span className="capitalize">
                                                                {
                                                                    student.weakest_topic
                                                                }
                                                            </span>
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <TrophyIcon className="mx-auto h-12 w-12 text-gray-400" />
                                        <h3 className="mt-2 text-sm font-medium text-gray-900">
                                            Great job!
                                        </h3>
                                        <p className="mt-1 text-sm text-gray-500">
                                            No students are currently
                                            struggling.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Top Performers */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-medium text-gray-900">
                                        Top Performers
                                    </h3>
                                    <TrophyIcon className="h-6 w-6 text-yellow-500" />
                                </div>
                                {topPerformers.length > 0 ? (
                                    <div className="space-y-4">
                                        {topPerformers.map((student, index) => (
                                            <div
                                                key={student.id}
                                                className="border rounded-lg p-4"
                                            >
                                                <div className="flex items-center justify-between mb-2">
                                                    <div className="flex items-center">
                                                        <span className="flex items-center justify-center w-6 h-6 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full mr-3">
                                                            {index + 1}
                                                        </span>
                                                        <Link
                                                            href={route(
                                                                "teacher.student.detail",
                                                                student.id
                                                            )}
                                                            className="font-medium text-blue-600 hover:text-blue-800"
                                                        >
                                                            {student.name}
                                                        </Link>
                                                    </div>
                                                    <span className="text-sm text-green-600 font-medium">
                                                        {
                                                            student.average_accuracy
                                                        }
                                                        % accuracy
                                                    </span>
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    <p>
                                                        Total points:{" "}
                                                        {student.total_points}
                                                    </p>
                                                    <p>
                                                        Quizzes taken:{" "}
                                                        {student.quiz_count}
                                                    </p>
                                                    {student.strongest_topic && (
                                                        <p>
                                                            Strongest topic:{" "}
                                                            <span className="capitalize">
                                                                {
                                                                    student.strongest_topic
                                                                }
                                                            </span>
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <ChartBarIcon className="mx-auto h-12 w-12 text-gray-400" />
                                        <h3 className="mt-2 text-sm font-medium text-gray-900">
                                            No data available
                                        </h3>
                                        <p className="mt-1 text-sm text-gray-500">
                                            Students need to complete more
                                            quizzes to appear here.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Topic Performance Trends */}
                    {Object.keys(topicTrends).length > 0 && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-6">
                                    Topic Performance Trends
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {Object.entries(topicTrends).map(
                                        ([topic, trends]) => (
                                            <div
                                                key={topic}
                                                className="border rounded-lg p-6"
                                            >
                                                <h4 className="text-lg font-medium text-gray-900 capitalize mb-4">
                                                    {topic}
                                                </h4>
                                                {trends.length > 0 ? (
                                                    <div className="space-y-2">
                                                        <div className="flex justify-between text-sm text-gray-600 mb-2">
                                                            <span>
                                                                Recent
                                                                Performance
                                                            </span>
                                                            <span>
                                                                Quiz Count
                                                            </span>
                                                        </div>
                                                        {trends
                                                            .slice(-7)
                                                            .map(
                                                                (
                                                                    trend,
                                                                    index
                                                                ) => (
                                                                    <div
                                                                        key={
                                                                            index
                                                                        }
                                                                        className="flex justify-between items-center text-sm"
                                                                    >
                                                                        <div className="flex items-center">
                                                                            <span className="text-gray-600 mr-2">
                                                                                {new Date(
                                                                                    trend.date
                                                                                ).toLocaleDateString(
                                                                                    "en-US",
                                                                                    {
                                                                                        month: "short",
                                                                                        day: "numeric",
                                                                                    }
                                                                                )}
                                                                            </span>
                                                                            <span
                                                                                className={`font-medium ${getPerformanceColor(
                                                                                    trend.accuracy
                                                                                )}`}
                                                                            >
                                                                                {
                                                                                    trend.accuracy
                                                                                }
                                                                                %
                                                                            </span>
                                                                        </div>
                                                                        <span className="text-gray-900">
                                                                            {
                                                                                trend.quiz_count
                                                                            }{" "}
                                                                            quizzes
                                                                        </span>
                                                                    </div>
                                                                )
                                                            )}
                                                    </div>
                                                ) : (
                                                    <div className="text-center py-4">
                                                        <p className="text-sm text-gray-500">
                                                            No recent activity
                                                        </p>
                                                    </div>
                                                )}
                                            </div>
                                        )
                                    )}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
