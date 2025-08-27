import { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import DangerButton from "@/Components/DangerButton";
import TextInput from "@/Components/TextInput";
import Modal from "@/Components/Modal";

export default function Index({
    auth,
    questions,
    filters,
    questionTypes,
    difficulties,
    gradeLevels,
}) {
    const [search, setSearch] = useState(filters.search || "");
    const [gradeFilter, setGradeFilter] = useState(filters.grade_level || "");
    const [typeFilter, setTypeFilter] = useState(filters.question_type || "");
    const [difficultyFilter, setDifficultyFilter] = useState(
        filters.difficulty || ""
    );
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [questionToDelete, setQuestionToDelete] = useState(null);

    const handleFilter = () => {
        router.get(
            route("teacher.questions.index"),
            {
                search,
                grade_level: gradeFilter,
                question_type: typeFilter,
                difficulty: difficultyFilter,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    };

    const clearFilters = () => {
        setSearch("");
        setGradeFilter("");
        setTypeFilter("");
        setDifficultyFilter("");
        router.get(route("teacher.questions.index"));
    };

    const confirmDelete = (question) => {
        setQuestionToDelete(question);
        setShowDeleteModal(true);
    };

    const deleteQuestion = () => {
        if (questionToDelete) {
            router.delete(
                route("teacher.questions.destroy", questionToDelete.id),
                {
                    onSuccess: () => {
                        setShowDeleteModal(false);
                        setQuestionToDelete(null);
                    },
                }
            );
        }
    };

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

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Question Management
                    </h2>
                    <Link href={route("teacher.questions.create")}>
                        <PrimaryButton>Add New Question</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Questions" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Filters */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">
                                Filters
                            </h3>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Search
                                    </label>
                                    <TextInput
                                        value={search}
                                        onChange={(e) =>
                                            setSearch(e.target.value)
                                        }
                                        placeholder="Search questions..."
                                        className="w-full"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Grade Level
                                    </label>
                                    <select
                                        value={gradeFilter}
                                        onChange={(e) =>
                                            setGradeFilter(e.target.value)
                                        }
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Grades</option>
                                        {gradeLevels.map((grade) => (
                                            <option
                                                key={grade.value}
                                                value={grade.value}
                                            >
                                                {grade.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Question Type
                                    </label>
                                    <select
                                        value={typeFilter}
                                        onChange={(e) =>
                                            setTypeFilter(e.target.value)
                                        }
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">All Types</option>
                                        {questionTypes.map((type) => (
                                            <option
                                                key={type.value}
                                                value={type.value}
                                            >
                                                {type.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Difficulty
                                    </label>
                                    <select
                                        value={difficultyFilter}
                                        onChange={(e) =>
                                            setDifficultyFilter(e.target.value)
                                        }
                                        className="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">
                                            All Difficulties
                                        </option>
                                        {difficulties.map((difficulty) => (
                                            <option
                                                key={difficulty.value}
                                                value={difficulty.value}
                                            >
                                                {difficulty.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex items-end gap-2">
                                    <SecondaryButton
                                        onClick={handleFilter}
                                        className="flex-1"
                                    >
                                        Filter
                                    </SecondaryButton>
                                    <SecondaryButton onClick={clearFilters}>
                                        Clear
                                    </SecondaryButton>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Questions List */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Question
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Grade
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Type
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Difficulty
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Competency
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {questions.data.map((question) => (
                                            <tr
                                                key={question.id}
                                                className="hover:bg-gray-50"
                                            >
                                                <td className="px-6 py-4">
                                                    <div className="text-sm text-gray-900 max-w-xs truncate">
                                                        {question.question_text}
                                                    </div>
                                                    <div className="text-sm text-gray-500">
                                                        Answer:{" "}
                                                        {
                                                            question.correct_answer
                                                        }
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Grade{" "}
                                                        {question.grade_level}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getTypeColor(
                                                            question.question_type
                                                        )}`}
                                                    >
                                                        {
                                                            questionTypes.find(
                                                                (t) =>
                                                                    t.value ===
                                                                    question.question_type
                                                            )?.label
                                                        }
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <span
                                                        className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getDifficultyColor(
                                                            question.difficulty
                                                        )}`}
                                                    >
                                                        {
                                                            difficulties.find(
                                                                (d) =>
                                                                    d.value ===
                                                                    question.difficulty
                                                            )?.label
                                                        }
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="text-sm text-gray-900 max-w-xs truncate">
                                                        {
                                                            question.deped_competency
                                                        }
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <Link
                                                        href={route(
                                                            "teacher.questions.show",
                                                            question.id
                                                        )}
                                                        className="text-indigo-600 hover:text-indigo-900"
                                                    >
                                                        View
                                                    </Link>
                                                    <Link
                                                        href={route(
                                                            "teacher.questions.edit",
                                                            question.id
                                                        )}
                                                        className="text-yellow-600 hover:text-yellow-900"
                                                    >
                                                        Edit
                                                    </Link>
                                                    <button
                                                        onClick={() =>
                                                            confirmDelete(
                                                                question
                                                            )
                                                        }
                                                        className="text-red-600 hover:text-red-900"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination */}
                            {questions.links && (
                                <div className="mt-6 flex justify-between items-center">
                                    <div className="text-sm text-gray-700">
                                        Showing {questions.from} to{" "}
                                        {questions.to} of {questions.total}{" "}
                                        results
                                    </div>
                                    <div className="flex space-x-1">
                                        {questions.links.map((link, index) => (
                                            <Link
                                                key={index}
                                                href={link.url}
                                                className={`px-3 py-2 text-sm rounded-md ${
                                                    link.active
                                                        ? "bg-indigo-600 text-white"
                                                        : "bg-white text-gray-700 hover:bg-gray-50 border border-gray-300"
                                                }`}
                                                dangerouslySetInnerHTML={{
                                                    __html: link.label,
                                                }}
                                            />
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Delete Confirmation Modal */}
            <Modal
                show={showDeleteModal}
                onClose={() => setShowDeleteModal(false)}
            >
                <div className="p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                        Delete Question
                    </h3>
                    <p className="text-sm text-gray-600 mb-6">
                        Are you sure you want to delete this question? This
                        action cannot be undone.
                    </p>
                    <div className="flex justify-end space-x-3">
                        <SecondaryButton
                            onClick={() => setShowDeleteModal(false)}
                        >
                            Cancel
                        </SecondaryButton>
                        <DangerButton onClick={deleteQuestion}>
                            Delete Question
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </AuthenticatedLayout>
    );
}
