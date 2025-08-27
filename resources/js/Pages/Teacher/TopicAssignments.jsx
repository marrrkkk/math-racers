import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";
import { useState } from "react";
import {
    AcademicCapIcon,
    UserGroupIcon,
    CheckIcon,
    XMarkIcon,
} from "@heroicons/react/24/outline";

export default function TopicAssignments({
    students,
    availableTopics,
    assignments,
    filters,
    availableGrades,
}) {
    const [selectedStudents, setSelectedStudents] = useState([]);
    const [selectedTopics, setSelectedTopics] = useState([]);
    const [showAssignmentForm, setShowAssignmentForm] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        student_ids: [],
        topics: [],
        grade_level: filters.grade,
        due_date: "",
    });

    const handleFilterChange = (key, value) => {
        const newFilters = { ...filters, [key]: value };
        router.get(route("teacher.topic-assignments"), newFilters, {
            preserveState: true,
            replace: true,
        });
    };

    const handleStudentSelect = (studentId) => {
        setSelectedStudents((prev) => {
            const newSelection = prev.includes(studentId)
                ? prev.filter((id) => id !== studentId)
                : [...prev, studentId];
            setData("student_ids", newSelection);
            return newSelection;
        });
    };

    const handleTopicSelect = (topic) => {
        setSelectedTopics((prev) => {
            const newSelection = prev.includes(topic)
                ? prev.filter((t) => t !== topic)
                : [...prev, topic];
            setData("topics", newSelection);
            return newSelection;
        });
    };

    const handleSelectAll = () => {
        if (selectedStudents.length === students.length) {
            setSelectedStudents([]);
            setData("student_ids", []);
        } else {
            const allIds = students.map((s) => s.id);
            setSelectedStudents(allIds);
            setData("student_ids", allIds);
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route("teacher.assign-topics"), {
            onSuccess: () => {
                setSelectedStudents([]);
                setSelectedTopics([]);
                setShowAssignmentForm(false);
                reset();
            },
        });
    };

    const getMasteryColor = (level) => {
        if (level >= 80) return "text-green-600 bg-green-100";
        if (level >= 60) return "text-yellow-600 bg-yellow-100";
        return "text-red-600 bg-red-100";
    };

    const getTopicIcon = (topic) => {
        const icons = {
            addition: "+",
            subtraction: "−",
            multiplication: "×",
            division: "÷",
        };
        return icons[topic] || "?";
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Topic Assignments - Grade {filters.grade}
                    </h2>
                    <div className="flex space-x-4">
                        <button
                            onClick={() =>
                                setShowAssignmentForm(!showAssignmentForm)
                            }
                            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                        >
                            {showAssignmentForm
                                ? "Cancel Assignment"
                                : "Assign Topics"}
                        </button>
                        <Link
                            href={route("teacher.dashboard")}
                            className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium"
                        >
                            Back to Dashboard
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title="Topic Assignments" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    {/* Grade Filter */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center space-x-4">
                                <label className="text-sm font-medium text-gray-700">
                                    Select Grade:
                                </label>
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
                                    {availableGrades.map((grade) => (
                                        <option key={grade} value={grade}>
                                            Grade {grade}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Assignment Form */}
                    {showAssignmentForm && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Assign Topics to Students
                                </h3>
                                <form
                                    onSubmit={handleSubmit}
                                    className="space-y-6"
                                >
                                    {/* Topic Selection */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Select Topics to Assign
                                        </label>
                                        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            {Object.entries(
                                                availableTopics
                                            ).map(([topic, label]) => (
                                                <label
                                                    key={topic}
                                                    className="flex items-center"
                                                >
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedTopics.includes(
                                                            topic
                                                        )}
                                                        onChange={() =>
                                                            handleTopicSelect(
                                                                topic
                                                            )
                                                        }
                                                        className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    />
                                                    <span className="ml-2 text-sm text-gray-700 flex items-center">
                                                        <span className="inline-flex items-center justify-center w-6 h-6 bg-blue-100 text-blue-600 text-sm font-bold rounded mr-2">
                                                            {getTopicIcon(
                                                                topic
                                                            )}
                                                        </span>
                                                        {label}
                                                    </span>
                                                </label>
                                            ))}
                                        </div>
                                        {errors.topics && (
                                            <p className="mt-1 text-sm text-red-600">
                                                {errors.topics}
                                            </p>
                                        )}
                                    </div>

                                    {/* Due Date (Optional) */}
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-2">
                                            Due Date (Optional)
                                        </label>
                                        <input
                                            type="date"
                                            value={data.due_date}
                                            onChange={(e) =>
                                                setData(
                                                    "due_date",
                                                    e.target.value
                                                )
                                            }
                                            className="border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        />
                                    </div>

                                    {/* Submit Button */}
                                    <div className="flex justify-end space-x-4">
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setShowAssignmentForm(false)
                                            }
                                            className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="submit"
                                            disabled={
                                                processing ||
                                                selectedStudents.length === 0 ||
                                                selectedTopics.length === 0
                                            }
                                            className="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {processing
                                                ? "Assigning..."
                                                : `Assign to ${selectedStudents.length} Students`}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    )}

                    {/* Students List */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-medium text-gray-900">
                                    Students in Grade {filters.grade} (
                                    {students.length} students)
                                </h3>
                                {showAssignmentForm && (
                                    <button
                                        onClick={handleSelectAll}
                                        className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                    >
                                        {selectedStudents.length ===
                                        students.length
                                            ? "Deselect All"
                                            : "Select All"}
                                    </button>
                                )}
                            </div>

                            {students.length > 0 ? (
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {students.map((student) => (
                                        <div
                                            key={student.id}
                                            className={`border rounded-lg p-4 transition-colors ${
                                                showAssignmentForm &&
                                                selectedStudents.includes(
                                                    student.id
                                                )
                                                    ? "border-blue-500 bg-blue-50"
                                                    : "border-gray-200 hover:border-gray-300"
                                            }`}
                                        >
                                            <div className="flex items-center justify-between mb-3">
                                                <div className="flex items-center">
                                                    {showAssignmentForm && (
                                                        <input
                                                            type="checkbox"
                                                            checked={selectedStudents.includes(
                                                                student.id
                                                            )}
                                                            onChange={() =>
                                                                handleStudentSelect(
                                                                    student.id
                                                                )
                                                            }
                                                            className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mr-3"
                                                        />
                                                    )}
                                                    <div className="flex-shrink-0 h-10 w-10">
                                                        <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <span className="text-sm font-medium text-blue-600">
                                                                {student.name
                                                                    .charAt(0)
                                                                    .toUpperCase()}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div className="ml-3">
                                                        <p className="text-sm font-medium text-gray-900">
                                                            {student.name}
                                                        </p>
                                                    </div>
                                                </div>
                                                <Link
                                                    href={route(
                                                        "teacher.student.detail",
                                                        student.id
                                                    )}
                                                    className="text-blue-600 hover:text-blue-800 text-sm"
                                                >
                                                    View Details
                                                </Link>
                                            </div>

                                            {/* Current Progress */}
                                            <div className="space-y-2">
                                                <p className="text-xs font-medium text-gray-600 uppercase tracking-wide">
                                                    Current Progress
                                                </p>
                                                {Object.entries(
                                                    availableTopics
                                                ).map(([topic, label]) => {
                                                    const progress =
                                                        student.progress[topic];
                                                    return (
                                                        <div
                                                            key={topic}
                                                            className="flex items-center justify-between text-sm"
                                                        >
                                                            <div className="flex items-center">
                                                                <span className="inline-flex items-center justify-center w-5 h-5 bg-gray-100 text-gray-600 text-xs font-bold rounded mr-2">
                                                                    {getTopicIcon(
                                                                        topic
                                                                    )}
                                                                </span>
                                                                <span className="text-gray-700">
                                                                    {label}
                                                                </span>
                                                            </div>
                                                            {progress ? (
                                                                <div className="flex items-center space-x-2">
                                                                    <span
                                                                        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getMasteryColor(
                                                                            progress.mastery_level
                                                                        )}`}
                                                                    >
                                                                        {progress.mastery_level.toFixed(
                                                                            0
                                                                        )}
                                                                        %
                                                                    </span>
                                                                    <span className="text-gray-500 text-xs">
                                                                        {
                                                                            progress.total_points
                                                                        }{" "}
                                                                        pts
                                                                    </span>
                                                                </div>
                                                            ) : (
                                                                <span className="text-gray-400 text-xs">
                                                                    Not started
                                                                </span>
                                                            )}
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <UserGroupIcon className="mx-auto h-12 w-12 text-gray-400" />
                                    <h3 className="mt-2 text-sm font-medium text-gray-900">
                                        No students found
                                    </h3>
                                    <p className="mt-1 text-sm text-gray-500">
                                        No students are enrolled in Grade{" "}
                                        {filters.grade}.
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Current Assignments Overview */}
                    {assignments.length > 0 && (
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div className="p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Recent Topic Assignments
                                </h3>
                                <div className="space-y-4">
                                    {assignments
                                        .slice(0, 10)
                                        .map((assignment, index) => (
                                            <div
                                                key={index}
                                                className="border rounded-lg p-4"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <div>
                                                        <p className="font-medium text-gray-900">
                                                            {
                                                                assignment
                                                                    .student
                                                                    .name
                                                            }
                                                        </p>
                                                        <div className="flex items-center space-x-2 mt-1">
                                                            {assignment.topics.map(
                                                                (topic) => (
                                                                    <span
                                                                        key={
                                                                            topic
                                                                        }
                                                                        className="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                                                                    >
                                                                        <span className="mr-1">
                                                                            {getTopicIcon(
                                                                                topic
                                                                            )}
                                                                        </span>
                                                                        {
                                                                            availableTopics[
                                                                                topic
                                                                            ]
                                                                        }
                                                                    </span>
                                                                )
                                                            )}
                                                        </div>
                                                    </div>
                                                    <div className="text-right">
                                                        <p className="text-sm text-gray-500">
                                                            Updated:{" "}
                                                            {new Date(
                                                                assignment.last_updated
                                                            ).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
