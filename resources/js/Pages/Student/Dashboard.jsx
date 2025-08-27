import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import PlayfulButton from "@/Components/PlayfulButton";
import { Head, Link, useForm } from "@inertiajs/react";
import { useState } from "react";

export default function StudentDashboard({ user }) {
    const [selectedGrade, setSelectedGrade] = useState(
        user.grade_level || null
    );
    const { post, processing } = useForm();

    const handleGradeSelection = (grade) => {
        setSelectedGrade(grade);
        post(route("student.select-grade"), {
            data: { grade_level: grade },
        });
    };

    const grades = [
        {
            level: 1,
            title: "Grade 1",
            description: "Basic addition and subtraction",
            color: "bg-green-100 hover:bg-green-200 border-green-300",
            textColor: "text-green-800",
        },
        {
            level: 2,
            title: "Grade 2",
            description: "Addition, subtraction, and basic multiplication",
            color: "bg-blue-100 hover:bg-blue-200 border-blue-300",
            textColor: "text-blue-800",
        },
        {
            level: 3,
            title: "Grade 3",
            description: "All four operations: +, -, ×, ÷",
            color: "bg-purple-100 hover:bg-purple-200 border-purple-300",
            textColor: "text-purple-800",
        },
    ];

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    🏁 Math Racers Dashboard
                </h2>
            }
        >
            <Head title="Student Dashboard" />

            <div className="py-6 sm:py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Welcome Section */}
                    <div className="mb-8 text-center">
                        <h1 className="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                            🏎️ Welcome, {user.name}!
                        </h1>
                        <p className="text-lg text-gray-600 mb-2">
                            Ready to race and learn math?
                        </p>
                        <p className="text-base text-gray-500">
                            Choose your grade level to start your math racing
                            adventure!
                        </p>
                    </div>

                    {/* Current Grade Display */}
                    {user.grade_level && (
                        <div className="mb-8 text-center">
                            <div className="inline-flex items-center px-4 py-2 bg-yellow-100 border border-yellow-300 rounded-full">
                                <span className="text-yellow-800 font-medium">
                                    🎯 Current Grade: {user.grade_level}
                                </span>
                            </div>
                        </div>
                    )}

                    {/* Grade Selection Cards */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        {grades.map((grade) => (
                            <div
                                key={grade.level}
                                className={`
                                    relative overflow-hidden rounded-xl border-2 p-6 cursor-pointer transition-all duration-200 transform hover:scale-105 hover:shadow-lg
                                    ${grade.color}
                                    ${
                                        selectedGrade === grade.level
                                            ? "ring-4 ring-yellow-400 ring-opacity-50"
                                            : ""
                                    }
                                    ${
                                        processing
                                            ? "opacity-50 cursor-not-allowed"
                                            : ""
                                    }
                                `}
                                onClick={() =>
                                    !processing &&
                                    handleGradeSelection(grade.level)
                                }
                            >
                                {/* Racing Flag Icon */}
                                <div className="absolute top-2 right-2 text-2xl">
                                    🏁
                                </div>

                                <div className="text-center">
                                    <h3
                                        className={`text-2xl font-bold mb-2 ${grade.textColor}`}
                                    >
                                        {grade.title}
                                    </h3>
                                    <p
                                        className={`text-sm mb-4 ${grade.textColor} opacity-80`}
                                    >
                                        {grade.description}
                                    </p>

                                    {/* Racing Car Icon */}
                                    <div className="text-4xl mb-3">🏎️</div>

                                    <PlayfulButton
                                        variant="racing"
                                        size="normal"
                                        icon="🚀"
                                        soundEffect="racing"
                                        disabled={processing}
                                        className="w-full"
                                    >
                                        {processing &&
                                        selectedGrade === grade.level ? (
                                            <span className="flex items-center justify-center">
                                                <svg
                                                    className="animate-spin -ml-1 mr-3 h-5 w-5"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    fill="none"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <circle
                                                        className="opacity-25"
                                                        cx="12"
                                                        cy="12"
                                                        r="10"
                                                        stroke="currentColor"
                                                        strokeWidth="4"
                                                    ></circle>
                                                    <path
                                                        className="opacity-75"
                                                        fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                    ></path>
                                                </svg>
                                                Starting...
                                            </span>
                                        ) : (
                                            "Start Racing!"
                                        )}
                                    </PlayfulButton>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Quick Actions */}
                    {user.grade_level && (
                        <div className="mb-8">
                            <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <span className="mr-2">⚡</span>
                                Quick Actions
                            </h3>
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <Link
                                    href={route(
                                        "student.topics",
                                        user.grade_level
                                    )}
                                    className="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl p-4 text-center transition-all duration-200 transform hover:scale-105"
                                >
                                    <div className="text-3xl mb-2">🎯</div>
                                    <div className="font-semibold">
                                        Start Quiz
                                    </div>
                                    <div className="text-sm opacity-90">
                                        Practice math topics
                                    </div>
                                </Link>
                                <Link
                                    href={route("student.progress")}
                                    className="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-xl p-4 text-center transition-all duration-200 transform hover:scale-105"
                                >
                                    <div className="text-3xl mb-2">📊</div>
                                    <div className="font-semibold">
                                        My Progress
                                    </div>
                                    <div className="text-sm opacity-90">
                                        View achievements
                                    </div>
                                </Link>
                                <Link
                                    href={route("student.leaderboard")}
                                    className="bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white rounded-xl p-4 text-center transition-all duration-200 transform hover:scale-105"
                                >
                                    <div className="text-3xl mb-2">🏆</div>
                                    <div className="font-semibold">
                                        Leaderboard
                                    </div>
                                    <div className="text-sm opacity-90">
                                        See rankings
                                    </div>
                                </Link>
                            </div>
                        </div>
                    )}

                    {/* Instructions */}
                    <div className="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-6">
                        <h3 className="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                            <span className="mr-2">📚</span>
                            How to Play Math Racers
                        </h3>
                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-gray-700">
                            <div className="flex items-start">
                                <span className="mr-2 text-lg">1️⃣</span>
                                <span>Choose your grade level</span>
                            </div>
                            <div className="flex items-start">
                                <span className="mr-2 text-lg">2️⃣</span>
                                <span>Select a math topic</span>
                            </div>
                            <div className="flex items-start">
                                <span className="mr-2 text-lg">3️⃣</span>
                                <span>Answer questions correctly</span>
                            </div>
                            <div className="flex items-start">
                                <span className="mr-2 text-lg">4️⃣</span>
                                <span>Watch your racer advance!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
