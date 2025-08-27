import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";
import {
    MagnifyingGlassIcon,
    TrophyIcon,
    ClockIcon,
    AcademicCapIcon,
    ChartBarIcon,
} from "@heroicons/react/24/outline";

export default function StudentPerformance({
    students,
    filters,
    availableGrades,
    availableTopics,
}) {
    const [searchTerm, setSearchTerm] = useState(filters.search || "");

    const handleFilterChange = (key, value) => {
        const newFilters = { ...filters, [key]: value };
        if (key === "search") {
            newFilters.search = searchTerm;
        }
        router.get(route("teacher.student-performance"), newFilters, {
            preserveState: true,
            replace: true,
        });
    };

    const handleSearch = (e) => {
        e.preventDefault();
        handleFilterChange("search", searchTerm);
    };

    const getPerformanceColor = (accuracy) => {
        if (accuracy >= 80) return "text-green-600 bg-green-100";
        if (accuracy >= 60) return "text-yellow-600 bg-yellow-100";
        return "text-red-600 bg-red-100";
    };

    const getActivityStatus = (lastActivity) => {
        if (!lastActivity) return "Never";

        const daysSince = Math.floor(
            (new Date() - new Date(lastActivity)) / (1000 * 60 * 60 * 24)
        );
        if (daysSince === 0) return "Today";
        if (daysSince === 1) return "Yesterday";
        if (daysSince <= 7) return `${daysSince} days ago`;
        return "Over a week ago";
    };

    const formatDate = (dateString) => {
        if (!dateString) return "Never";
        return new Date(dateString).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric",
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Student Performance Analytics
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
            <Head title="Student Performance" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Filters */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                {/* Search */}
                                <form
                                    onSubmit={handleSearch}
                                    className="relative"
                                >
                                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <input
                                        type="text"
                                        placeholder="Search students..."
                                        value={searchTerm}
                                        onChange={(e) =>
                                            setSearchTerm(e.target.value)
                                        }
                                        className="pl-10 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </form>

                                {/* Grade Filter */}
                                <select
                                    value={filters.grade}
                                    onChange={(e) =>
                                        handleFilterChange(
                                            "grade",
                                            e.target.value
                                        )
                                    }
                                    className="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    {Object.entries(availableGrades).map(
                                        ([value, label]) => (
                                            <option key={value} value={value}>
                                                {label}
                                            </option>
                                        )
                                    )}
                                </select>

                                {/* Topic Filter */}
                                <select
                                    value={filters.topic}
                                    onChange={(e) =>
                                        handleFilterChange(
                                            "topic",
                                            e.target.value
                                        )
                                    }
                                    className="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    {Object.entries(availableTopics).map(
                                        ([value, label]) => (
                                            <option key={value} value={value}>
                                                {label}
                                            </option>
                                        )
                                    )}
                                </select>

                                {/* Quick Actions */}
                                <Link
                                    href={route("teacher.topic-assignments")}
                                    className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium text-center"
                                >
                                    Assign Topics
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Students Table */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Student Performance ({students.length}{" "}
                                    students)
                                </h3>
                                <div className="flex space-x-2">
                                    <Link
                                        href={route(
                                            "teacher.class-performance"
                                        )}
                                        className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                    >
                                        View Class Analytics →
                                    </Link>
                                </div>
                            </div>

                            {students.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Student
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Grade
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Total Points
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Quizzes
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Avg Accuracy
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Best Score
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Mastery Topics
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Badges
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Last Activity
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {students.map((student) => (
                                                <tr
                                                    key={student.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="flex items-center">
                                                            <div className="flex-shrink-0 h-10 w-10">
                                                                <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                    <span className="text-sm font-medium text-blue-600">
                                                                        {student.name
                                                                            .charAt(
                                                                                0
                                                                            )
                                                                            .toUpperCase()}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div className="ml-4">
                                                                <div className="text-sm font-medium text-gray-900">
                                                                    {
                                                                        student.name
                                                                    }
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        Grade{" "}
                                                        {student.grade_level}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <div className="flex items-center">
                                                            <TrophyIcon className="h-4 w-4 text-yellow-500 mr-1" />
                                                            {
                                                                student.total_points
                                                            }
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {student.total_quizzes}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                        <span
                                                            className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getPerformanceColor(
                                                                student.average_accuracy
                                                            )}`}
                                                        >
                                                            {student.average_accuracy.toFixed(
                                                                1
                                                            )}
                                                            %
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {student.best_score} pts
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        <div className="flex items-center">
                                                            <AcademicCapIcon className="h-4 w-4 text-green-500 mr-1" />
                                                            {
                                                                student.mastery_topics
                                                            }
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {student.total_badges}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        <div className="flex items-center">
                                                            <ClockIcon className="h-4 w-4 text-gray-400 mr-1" />
                                                            {getActivityStatus(
                                                                student.last_activity
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <Link
                                                            href={route(
                                                                "teacher.student.detail",
                                                                student.id
                                                            )}
                                                            className="text-blue-600 hover:text-blue-900"
                                                        >
                                                            View Details
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <ChartBarIcon className="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">
                                        No students found
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Try adjusting your filters or search
                                        criteria.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Summary Statistics */}
                    {students.length > 0 && (
                        <div className="mt-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <TrophyIcon className="h-8 w-8 text-yellow-500" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">
                                            Avg Points
                                        </p>
                                        <p className="text-2xl font-bold text-gray-900">
                                            {Math.round(
                                                students.reduce(
                                                    (sum, s) =>
                                                        sum + s.total_points,
                                                    0
                                                ) / students.length
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <ChartBarIcon className="h-8 w-8 text-blue-500" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">
                                            Avg Accuracy
                                        </p>
                                        <p className="text-2xl font-bold text-gray-900">
                                            {(
                                                students.reduce(
                                                    (sum, s) =>
                                                        sum +
                                                        s.average_accuracy,
                                                    0
                                                ) / students.length
                                            ).toFixed(1)}
                                            %
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <AcademicCapIcon className="h-8 w-8 text-green-500" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">
                                            Total Quizzes
                                        </p>
                                        <p className="text-2xl font-bold text-gray-900">
                                            {students.reduce(
                                                (sum, s) =>
                                                    sum + s.total_quizzes,
                                                0
                                            )}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                                <div className="flex items-center">
                                    <div className="flex-shrink-0">
                                        <ClockIcon className="h-8 w-8 text-purple-500" />
                                    </div>
                                    <div className="ml-4">
                                        <p className="text-sm font-medium text-gray-600">
                                            Active Students
                                        </p>
                                        <p className="text-2xl font-bold text-gray-900">
                                            {
                                                students.filter(
                                                    (s) =>
                                                        s.last_activity &&
                                                        new Date(
                                                            s.last_activity
                                                        ) >
                                                            new Date(
                                                                Date.now() -
                                                                    7 *
                                                                        24 *
                                                                        60 *
                                                                        60 *
                                                                        1000
                                                            )
                                                ).length
                                            }
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
