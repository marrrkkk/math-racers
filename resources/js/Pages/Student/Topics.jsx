import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router } from "@inertiajs/react";

export default function Topics({ user, grade, topics }) {
    const topicConfig = {
        addition: {
            title: "Addition",
            icon: "➕",
            description: "Add numbers together",
            color: "bg-green-100 hover:bg-green-200 border-green-300",
            textColor: "text-green-800",
            bgGradient: "from-green-400 to-green-600",
        },
        subtraction: {
            title: "Subtraction",
            icon: "➖",
            description: "Take numbers away",
            color: "bg-red-100 hover:bg-red-200 border-red-300",
            textColor: "text-red-800",
            bgGradient: "from-red-400 to-red-600",
        },
        multiplication: {
            title: "Multiplication",
            icon: "✖️",
            description: "Multiply numbers",
            color: "bg-blue-100 hover:bg-blue-200 border-blue-300",
            textColor: "text-blue-800",
            bgGradient: "from-blue-400 to-blue-600",
        },
        division: {
            title: "Division",
            icon: "➗",
            description: "Divide numbers",
            color: "bg-purple-100 hover:bg-purple-200 border-purple-300",
            textColor: "text-purple-800",
            bgGradient: "from-purple-400 to-purple-600",
        },
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        🏁 Grade {grade} Math Topics
                    </h2>
                    <Link
                        href={route("student.dashboard")}
                        className="text-sm text-gray-600 hover:text-gray-800 flex items-center"
                    >
                        ← Back to Dashboard
                    </Link>
                </div>
            }
        >
            <Head title={`Grade ${grade} Topics`} />

            <div className="py-6 sm:py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Header Section */}
                    <div className="mb-8 text-center">
                        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                            🎯 Choose Your Math Challenge!
                        </h1>
                        <p className="text-lg text-gray-600 mb-2">
                            Grade {grade} • {user.name}
                        </p>
                        <p className="text-base text-gray-500">
                            Select a topic to start your racing adventure
                        </p>
                    </div>

                    {/* Topics Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 mb-8 max-w-4xl mx-auto">
                        {topics.map((topic) => {
                            const config = topicConfig[topic];
                            return (
                                <div
                                    key={topic}
                                    className={`
                                        relative overflow-hidden rounded-xl border-2 p-8 cursor-pointer transition-all duration-300 transform hover:scale-105 hover:shadow-xl
                                        ${config.color}
                                        group
                                    `}
                                    onClick={() => {
                                        router.post(
                                            route("student.quiz.start"),
                                            {
                                                grade: grade,
                                                topic: topic,
                                            }
                                        );
                                    }}
                                >
                                    {/* Background Pattern */}
                                    <div className="absolute inset-0 opacity-5">
                                        <div className="absolute inset-0 bg-gradient-to-br from-white to-transparent"></div>
                                    </div>

                                    {/* Racing Flag */}
                                    <div className="absolute top-4 right-4 text-2xl opacity-60 group-hover:opacity-100 transition-opacity">
                                        🏁
                                    </div>

                                    <div className="relative text-center">
                                        {/* Topic Icon */}
                                        <div className="text-6xl mb-4 transform group-hover:scale-110 transition-transform duration-300">
                                            {config.icon}
                                        </div>

                                        {/* Topic Title */}
                                        <h3
                                            className={`text-2xl sm:text-3xl font-bold mb-3 ${config.textColor}`}
                                        >
                                            {config.title}
                                        </h3>

                                        {/* Description */}
                                        <p
                                            className={`text-base mb-6 ${config.textColor} opacity-80`}
                                        >
                                            {config.description}
                                        </p>

                                        {/* Racing Car */}
                                        <div className="text-4xl mb-4 transform group-hover:translate-x-2 transition-transform duration-300">
                                            🏎️
                                        </div>

                                        {/* Start Button */}
                                        <button
                                            className={`
                                            w-full py-3 px-6 rounded-lg font-bold text-white transition-all duration-300
                                            bg-gradient-to-r ${config.bgGradient}
                                            hover:shadow-lg transform hover:-translate-y-1
                                            group-hover:scale-105
                                        `}
                                        >
                                            Start Racing! 🚀
                                        </button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>

                    {/* Grade Level Info */}
                    <div className="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 max-w-2xl mx-auto">
                        <h3 className="text-lg font-semibold text-gray-800 mb-3 flex items-center justify-center">
                            <span className="mr-2">📊</span>
                            Grade {grade} Learning Goals
                        </h3>
                        <div className="text-sm text-gray-700 text-center">
                            {grade === 1 && (
                                <p>
                                    Master basic addition and subtraction with
                                    numbers up to 20
                                </p>
                            )}
                            {grade === 2 && (
                                <p>
                                    Build fluency in addition, subtraction, and
                                    introduction to multiplication
                                </p>
                            )}
                            {grade === 3 && (
                                <p>
                                    Develop skills in all four operations with
                                    larger numbers
                                </p>
                            )}
                        </div>
                    </div>

                    {/* Quick Navigation */}
                    <div className="mt-8 mb-6">
                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <Link
                                href={route("student.progress")}
                                className="inline-flex items-center justify-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200"
                            >
                                📊 View My Progress
                            </Link>
                            <Link
                                href={route("student.leaderboard")}
                                className="inline-flex items-center justify-center px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors duration-200"
                            >
                                🏆 View Leaderboard
                            </Link>
                        </div>
                    </div>

                    {/* Racing Tips */}
                    <div className="mt-8 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-6 max-w-4xl mx-auto">
                        <h3 className="text-lg font-semibold text-gray-800 mb-4 text-center flex items-center justify-center">
                            <span className="mr-2">💡</span>
                            Racing Tips
                        </h3>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-700">
                            <div className="text-center">
                                <div className="text-2xl mb-2">⚡</div>
                                <p>
                                    <strong>Speed Bonus:</strong> Answer quickly
                                    for extra points!
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl mb-2">🎯</div>
                                <p>
                                    <strong>Accuracy Counts:</strong> Correct
                                    answers move you forward!
                                </p>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl mb-2">🏆</div>
                                <p>
                                    <strong>Earn Badges:</strong> Complete
                                    topics to unlock achievements!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
