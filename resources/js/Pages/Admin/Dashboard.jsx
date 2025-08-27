import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";

export default function Dashboard({
    user,
    statistics,
    recentUsers,
    recentQuizSessions,
    questionStats,
    usersByRole,
}) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Admin Dashboard
                </h2>
            }
        >
            <Head title="Admin Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Statistics Cards */}
                    <div className="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-5">
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="text-sm font-medium text-gray-500">
                                Total Users
                            </div>
                            <div className="text-2xl font-bold text-gray-900">
                                {statistics.total_users}
                            </div>
                        </div>
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="text-sm font-medium text-gray-500">
                                Students
                            </div>
                            <div className="text-2xl font-bold text-blue-600">
                                {statistics.total_students}
                            </div>
                        </div>
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="text-sm font-medium text-gray-500">
                                Teachers
                            </div>
                            <div className="text-2xl font-bold text-green-600">
                                {statistics.total_teachers}
                            </div>
                        </div>
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="text-sm font-medium text-gray-500">
                                Questions
                            </div>
                            <div className="text-2xl font-bold text-purple-600">
                                {statistics.total_questions}
                            </div>
                        </div>
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div className="text-sm font-medium text-gray-500">
                                Quiz Sessions
                            </div>
                            <div className="text-2xl font-bold text-orange-600">
                                {statistics.total_quiz_sessions}
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {/* Recent Users */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6 border-b border-gray-200">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Recent Users
                                </h3>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {recentUsers.map((user) => (
                                        <div
                                            key={user.id}
                                            className="flex items-center justify-between"
                                        >
                                            <div>
                                                <div className="text-sm font-medium text-gray-900">
                                                    {user.name}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    {user.email}
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div
                                                    className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                        user.role === "student"
                                                            ? "bg-blue-100 text-blue-800"
                                                            : user.role ===
                                                              "teacher"
                                                            ? "bg-green-100 text-green-800"
                                                            : "bg-purple-100 text-purple-800"
                                                    }`}
                                                >
                                                    {user.role}
                                                </div>
                                                <div className="text-xs text-gray-500 mt-1">
                                                    {new Date(
                                                        user.created_at
                                                    ).toLocaleDateString()}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Recent Quiz Sessions */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6 border-b border-gray-200">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Recent Quiz Activity
                                </h3>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {recentQuizSessions.map((session) => (
                                        <div
                                            key={session.id}
                                            className="flex items-center justify-between"
                                        >
                                            <div>
                                                <div className="text-sm font-medium text-gray-900">
                                                    {session.student?.name ||
                                                        "Unknown Student"}
                                                </div>
                                                <div className="text-sm text-gray-500">
                                                    Grade {session.grade_level}{" "}
                                                    - {session.question_type}
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {session.correct_answers}/
                                                    {session.total_questions}
                                                </div>
                                                <div className="text-xs text-gray-500">
                                                    {session.points_earned} pts
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>

                        {/* Question Statistics */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6 border-b border-gray-200">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Question Distribution
                                </h3>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {Object.entries(questionStats).map(
                                        ([grade, topics]) => (
                                            <div key={grade}>
                                                <div className="text-sm font-medium text-gray-900 mb-2">
                                                    Grade {grade}
                                                </div>
                                                <div className="grid grid-cols-2 gap-2">
                                                    {topics.map((topic) => (
                                                        <div
                                                            key={
                                                                topic.question_type
                                                            }
                                                            className="text-xs text-gray-600"
                                                        >
                                                            {
                                                                topic.question_type
                                                            }
                                                            : {topic.count}
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )
                                    )}
                                </div>
                            </div>
                        </div>

                        {/* Users by Role */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6 border-b border-gray-200">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Users by Role
                                </h3>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    {Object.entries(usersByRole).map(
                                        ([role, count]) => (
                                            <div
                                                key={role}
                                                className="flex items-center justify-between"
                                            >
                                                <div className="text-sm font-medium text-gray-900 capitalize">
                                                    {role}s
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    {count}
                                                </div>
                                            </div>
                                        )
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
