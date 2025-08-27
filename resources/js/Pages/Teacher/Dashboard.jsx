import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link } from "@inertiajs/react";
import {
    AcademicCapIcon,
    UserGroupIcon,
    ChartBarIcon,
    ClockIcon,
    TrophyIcon,
    BookOpenIcon,
} from "@heroicons/react/24/outline";

export default function Dashboard({
    teacher,
    overview,
    recentQuizzes,
    gradeStats,
    topicPerformance,
}) {
    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString("en-US", {
            month: "short",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
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
                        Teacher Dashboard
                    </h2>
                    <div className="flex space-x-4">
                        <Link
                            href={route("teacher.questions.create")}
                            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                        >
                            Add Question
                        </Link>
                        <Link
                            href={route("teacher.student-performance")}
                            className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                        >
                            View All Students
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Teacher Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Overview Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <UserGroupIcon className="h-8 w-8 text-blue-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Total Students
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {overview.total_students}
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
                                        {overview.active_students}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <BookOpenIcon className="h-8 w-8 text-purple-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Total Questions
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {overview.total_questions}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="flex items-center">
                                <ClockIcon className="h-8 w-8 text-orange-600" />
                                <div className="ml-4">
                                    <p className="text-sm font-medium text-gray-600">
                                        Recent Quizzes
                                    </p>
                                    <p className="text-2xl font-bold text-gray-900">
                                        {overview.recent_quizzes_count}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Grade Level Statistics */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-lg font-medium text-gray-900">
                                        Grade Level Performance
                                    </h3>
                                    <Link
                                        href={route(
                                            "teacher.class-performance"
                                        )}
                                        className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                    >
                                        View Details →
                                    </Link>
                                </div>
                                <div className="space-y-4">
                                    {Object.entries(gradeStats).map(
                                        ([grade, stats]) => (
                                            <div
                                                key={grade}
                                                className="border rounded-lg p-4"
                                            >
                                                <div className="flex justify-between items-center mb-2">
                                                    <h4 className="font-medium text-gray-900">
                                                        Grade {grade}
                                                    </h4>
                                                    <span className="text-sm text-gray-600">
                                                        {stats.active_students}/
                                                        {stats.total_students}{" "}
                                                        active
                                                    </span>
                                                </div>
                                                <div className="grid grid-cols-3 gap-4 text-sm">
                                                    <div>
                                                        <p className="text-gray-600">
                                                            Quizzes
                                                        </p>
                                                        <p className="font-medium">
                                                            {
                                                                stats.total_quizzes
                                                            }
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-gray-600">
                                                            Avg Accuracy
                                                        </p>
                                                        <p
                                                            className={`font-medium ${getPerformanceColor(
                                                                stats.average_accuracy
                                                            )}`}
                                                        >
                                                            {stats.average_accuracy.toFixed(
                                                                1
                                                            )}
                                                            %
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-gray-600">
                                                            Avg Points
                                                        </p>
                                                        <p className="font-medium">
                                                            {stats.average_points.toFixed(
                                                                0
                                                            )}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        )
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Topic Performance */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Topic Performance Overview
                                </h3>
                                <div className="space-y-4">
                                    {Object.entries(topicPerformance).map(
                                        ([topic, performance]) => (
                                            <div
                                                key={topic}
                                                className="border rounded-lg p-4"
                                            >
                                                <div className="flex justify-between items-center mb-2">
                                                    <h4 className="font-medium text-gray-900 capitalize">
                                                        {topic}
                                                    </h4>
                                                    <TrophyIcon className="h-5 w-5 text-yellow-500" />
                                                </div>
                                                <div className="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <p className="text-gray-600">
                                                            Attempts
                                                        </p>
                                                        <p className="font-medium">
                                                            {
                                                                performance.total_attempts
                                                            }
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-gray-600">
                                                            Students
                                                        </p>
                                                        <p className="font-medium">
                                                            {
                                                                performance.students_attempted
                                                            }
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-gray-600">
                                                            Avg Accuracy
                                                        </p>
                                                        <p
                                                            className={`font-medium ${getPerformanceColor(
                                                                performance.average_accuracy
                                                            )}`}
                                                        >
                                                            {performance.average_accuracy.toFixed(
                                                                1
                                                            )}
                                                            %
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p className="text-gray-600">
                                                            Mastery
                                                        </p>
                                                        <p className="font-medium">
                                                            {
                                                                performance.mastery_rate
                                                            }{" "}
                                                            students
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        )
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Recent Quiz Activity */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Recent Quiz Activity
                                </h3>
                                <Link
                                    href={route("teacher.student-performance")}
                                    className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View All Activity →
                                </Link>
                            </div>
                            {recentQuizzes.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Student
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Topic
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Grade
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Score
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Accuracy
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Completed
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {recentQuizzes.map((quiz) => (
                                                <tr
                                                    key={quiz.id}
                                                    className="hover:bg-gray-50"
                                                >
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <Link
                                                            href={route(
                                                                "teacher.student.detail",
                                                                quiz.student.id
                                                            )}
                                                            className="text-blue-600 hover:text-blue-800 font-medium"
                                                        >
                                                            {quiz.student.name}
                                                        </Link>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                                                        {quiz.question_type}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        Grade{" "}
                                                        {
                                                            quiz.student
                                                                .grade_level
                                                        }
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {quiz.points_earned} pts
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                                                        <span
                                                            className={getPerformanceColor(
                                                                quiz.accuracy
                                                            )}
                                                        >
                                                            {quiz.accuracy.toFixed(
                                                                1
                                                            )}
                                                            %
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {formatDate(
                                                            quiz.completed_at
                                                        )}
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <AcademicCapIcon className="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">
                                        No recent quiz activity
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        Students haven't taken any quizzes in
                                        the last 7 days.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Quick Actions */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Quick Actions
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                <Link
                                    href={route("teacher.questions.index")}
                                    className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <BookOpenIcon className="h-6 w-6 text-blue-600 mr-3" />
                                    <span className="text-sm font-medium text-gray-900">
                                        Manage Questions
                                    </span>
                                </Link>
                                <Link
                                    href={route("teacher.topic-assignments")}
                                    className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <AcademicCapIcon className="h-6 w-6 text-green-600 mr-3" />
                                    <span className="text-sm font-medium text-gray-900">
                                        Assign Topics
                                    </span>
                                </Link>
                                <Link
                                    href={route("teacher.class-performance")}
                                    className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <ChartBarIcon className="h-6 w-6 text-purple-600 mr-3" />
                                    <span className="text-sm font-medium text-gray-900">
                                        Class Analytics
                                    </span>
                                </Link>
                                <Link
                                    href={route("teacher.student-performance")}
                                    className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                                >
                                    <UserGroupIcon className="h-6 w-6 text-orange-600 mr-3" />
                                    <span className="text-sm font-medium text-gray-900">
                                        Student Progress
                                    </span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
