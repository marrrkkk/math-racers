import { Head, Link } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import SecondaryButton from "@/Components/SecondaryButton";

export default function Show({ auth, question }) {
    const getDifficultyColor = (difficulty) => {
        switch (difficulty) {
            case "easy":
                return "bg-green-100 text-green-800";
            case "medium":
                return "bg-yellow-100 text-yellow-800";
            case "hard":
                return "bg-red-100 text-red-800";
            default:
                return "bg-gray-100 text-gray-800";
        }
    };

    const getTypeColor = (type) => {
        switch (type) {
            case "addition":
                return "bg-blue-100 text-blue-800";
            case "subtraction":
                return "bg-purple-100 text-purple-800";
            case "multiplication":
                return "bg-indigo-100 text-indigo-800";
            case "division":
                return "bg-pink-100 text-pink-800";
            default:
                return "bg-gray-100 text-gray-800";
        }
    };

    const getTypeLabel = (type) => {
        const types = {
            addition: "Addition",
            subtraction: "Subtraction",
            multiplication: "Multiplication",
            division: "Division",
        };
        return types[type] || type;
    };

    const getDifficultyLabel = (difficulty) => {
        const difficulties = {
            easy: "Easy",
            medium: "Medium",
            hard: "Hard",
        };
        return difficulties[difficulty] || difficulty;
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Question Details
                    </h2>
                    <div className="flex space-x-3">
                        <Link
                            href={route("teacher.questions.edit", question.id)}
                        >
                            <SecondaryButton>Edit Question</SecondaryButton>
                        </Link>
                        <Link href={route("teacher.questions.index")}>
                            <SecondaryButton>Back to Questions</SecondaryButton>
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Question Details" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Question Header */}
                            <div className="mb-6">
                                <div className="flex flex-wrap gap-2 mb-4">
                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Grade {question.grade_level}
                                    </span>
                                    <span
                                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTypeColor(
                                            question.question_type
                                        )}`}
                                    >
                                        {getTypeLabel(question.question_type)}
                                    </span>
                                    <span
                                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getDifficultyColor(
                                            question.difficulty
                                        )}`}
                                    >
                                        {getDifficultyLabel(
                                            question.difficulty
                                        )}
                                    </span>
                                </div>
                            </div>

                            {/* Question Content */}
                            <div className="space-y-6">
                                {/* Question Text */}
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Question
                                    </h3>
                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <p className="text-gray-800 text-lg leading-relaxed">
                                            {question.question_text}
                                        </p>
                                    </div>
                                </div>

                                {/* Correct Answer */}
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        Correct Answer
                                    </h3>
                                    <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <p className="text-green-800 font-semibold text-lg">
                                            {question.correct_answer}
                                        </p>
                                    </div>
                                </div>

                                {/* Multiple Choice Options */}
                                {question.options &&
                                    question.options.length > 0 && (
                                        <div>
                                            <h3 className="text-lg font-medium text-gray-900 mb-2">
                                                Answer Options
                                            </h3>
                                            <div className="space-y-2">
                                                {question.options.map(
                                                    (option, index) => (
                                                        <div
                                                            key={index}
                                                            className={`p-3 rounded-lg border ${
                                                                option.toLowerCase() ===
                                                                question.correct_answer.toLowerCase()
                                                                    ? "bg-green-50 border-green-200 text-green-800"
                                                                    : "bg-gray-50 border-gray-200 text-gray-800"
                                                            }`}
                                                        >
                                                            <span className="font-medium mr-2">
                                                                {String.fromCharCode(
                                                                    65 + index
                                                                )}
                                                                .
                                                            </span>
                                                            {option}
                                                            {option.toLowerCase() ===
                                                                question.correct_answer.toLowerCase() && (
                                                                <span className="ml-2 text-green-600 font-semibold">
                                                                    ✓ Correct
                                                                </span>
                                                            )}
                                                        </div>
                                                    )
                                                )}
                                            </div>
                                        </div>
                                    )}

                                {/* DepEd Competency */}
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-2">
                                        DepEd Competency
                                    </h3>
                                    <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p className="text-blue-800">
                                            {question.deped_competency}
                                        </p>
                                    </div>
                                </div>

                                {/* Question Metadata */}
                                <div className="border-t pt-6">
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                                        Question Information
                                    </h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">
                                                Created By
                                            </dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                {question.creator
                                                    ? question.creator.name
                                                    : "Unknown"}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">
                                                Created Date
                                            </dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                {new Date(
                                                    question.created_at
                                                ).toLocaleDateString()}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">
                                                Last Updated
                                            </dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                {new Date(
                                                    question.updated_at
                                                ).toLocaleDateString()}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt className="text-sm font-medium text-gray-500">
                                                Question ID
                                            </dt>
                                            <dd className="mt-1 text-sm text-gray-900">
                                                #{question.id}
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
