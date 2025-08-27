import { useState, useEffect } from "react";
import { Head, Link, useForm } from "@inertiajs/react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import InputLabel from "@/Components/InputLabel";
import InputError from "@/Components/InputError";

export default function Edit({
    auth,
    question,
    questionTypes,
    difficulties,
    gradeLevels,
    depedCompetencies,
}) {
    const { data, setData, put, processing, errors } = useForm({
        question_text: question.question_text || "",
        question_type: question.question_type || "",
        grade_level: question.grade_level || "",
        difficulty: question.difficulty || "",
        correct_answer: question.correct_answer || "",
        options: question.options || ["", "", "", ""],
        deped_competency: question.deped_competency || "",
    });

    const [isMultipleChoice, setIsMultipleChoice] = useState(
        question.options && question.options.length > 0
    );
    const [availableCompetencies, setAvailableCompetencies] = useState([]);

    useEffect(() => {
        updateCompetencies(data.grade_level, data.question_type);
    }, []);

    const handleGradeChange = (gradeLevel) => {
        setData("grade_level", gradeLevel);
        updateCompetencies(gradeLevel, data.question_type);
    };

    const handleTypeChange = (questionType) => {
        setData("question_type", questionType);
        updateCompetencies(data.grade_level, questionType);
    };

    const updateCompetencies = (gradeLevel, questionType) => {
        if (
            gradeLevel &&
            questionType &&
            depedCompetencies[gradeLevel] &&
            depedCompetencies[gradeLevel][questionType]
        ) {
            setAvailableCompetencies(
                depedCompetencies[gradeLevel][questionType]
            );
        } else {
            setAvailableCompetencies([]);
        }
    };

    const handleOptionChange = (index, value) => {
        const newOptions = [...data.options];
        newOptions[index] = value;
        setData("options", newOptions);
    };

    const toggleMultipleChoice = () => {
        setIsMultipleChoice(!isMultipleChoice);
        if (!isMultipleChoice) {
            setData("options", question.options || ["", "", "", ""]);
        } else {
            setData("options", []);
        }
    };

    const submit = (e) => {
        e.preventDefault();

        const submitData = {
            ...data,
            options: isMultipleChoice
                ? data.options.filter((option) => option.trim() !== "")
                : null,
        };

        put(route("teacher.questions.update", question.id), {
            data: submitData,
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Edit Question
                    </h2>
                    <Link href={route("teacher.questions.index")}>
                        <SecondaryButton>Back to Questions</SecondaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Edit Question" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={submit} className="space-y-6">
                                {/* Question Text */}
                                <div>
                                    <InputLabel
                                        htmlFor="question_text"
                                        value="Question Text"
                                    />
                                    <textarea
                                        id="question_text"
                                        value={data.question_text}
                                        onChange={(e) =>
                                            setData(
                                                "question_text",
                                                e.target.value
                                            )
                                        }
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows="3"
                                        placeholder="Enter the math question..."
                                        required
                                    />
                                    <InputError
                                        message={errors.question_text}
                                        className="mt-2"
                                    />
                                </div>

                                {/* Grade Level and Question Type */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel
                                            htmlFor="grade_level"
                                            value="Grade Level"
                                        />
                                        <select
                                            id="grade_level"
                                            value={data.grade_level}
                                            onChange={(e) =>
                                                handleGradeChange(
                                                    e.target.value
                                                )
                                            }
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                            <option value="">
                                                Select Grade Level
                                            </option>
                                            {gradeLevels.map((grade) => (
                                                <option
                                                    key={grade.value}
                                                    value={grade.value}
                                                >
                                                    {grade.label}
                                                </option>
                                            ))}
                                        </select>
                                        <InputError
                                            message={errors.grade_level}
                                            className="mt-2"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel
                                            htmlFor="question_type"
                                            value="Question Type"
                                        />
                                        <select
                                            id="question_type"
                                            value={data.question_type}
                                            onChange={(e) =>
                                                handleTypeChange(e.target.value)
                                            }
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                            <option value="">
                                                Select Question Type
                                            </option>
                                            {questionTypes.map((type) => (
                                                <option
                                                    key={type.value}
                                                    value={type.value}
                                                >
                                                    {type.label} ({type.symbol})
                                                </option>
                                            ))}
                                        </select>
                                        <InputError
                                            message={errors.question_type}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>

                                {/* Difficulty */}
                                <div>
                                    <InputLabel
                                        htmlFor="difficulty"
                                        value="Difficulty Level"
                                    />
                                    <select
                                        id="difficulty"
                                        value={data.difficulty}
                                        onChange={(e) =>
                                            setData(
                                                "difficulty",
                                                e.target.value
                                            )
                                        }
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option value="">
                                            Select Difficulty
                                        </option>
                                        {difficulties.map((difficulty) => (
                                            <option
                                                key={difficulty.value}
                                                value={difficulty.value}
                                            >
                                                {difficulty.label} (
                                                {difficulty.points} points)
                                            </option>
                                        ))}
                                    </select>
                                    <InputError
                                        message={errors.difficulty}
                                        className="mt-2"
                                    />
                                </div>

                                {/* Correct Answer */}
                                <div>
                                    <InputLabel
                                        htmlFor="correct_answer"
                                        value="Correct Answer"
                                    />
                                    <TextInput
                                        id="correct_answer"
                                        value={data.correct_answer}
                                        onChange={(e) =>
                                            setData(
                                                "correct_answer",
                                                e.target.value
                                            )
                                        }
                                        className="mt-1 block w-full"
                                        placeholder="Enter the correct answer..."
                                        required
                                    />
                                    <InputError
                                        message={errors.correct_answer}
                                        className="mt-2"
                                    />
                                </div>

                                {/* Multiple Choice Options */}
                                <div>
                                    <div className="flex items-center mb-3">
                                        <input
                                            type="checkbox"
                                            id="multiple_choice"
                                            checked={isMultipleChoice}
                                            onChange={toggleMultipleChoice}
                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <label
                                            htmlFor="multiple_choice"
                                            className="ml-2 text-sm text-gray-700"
                                        >
                                            Make this a multiple choice question
                                        </label>
                                    </div>

                                    {isMultipleChoice && (
                                        <div className="space-y-3">
                                            <InputLabel value="Answer Options (include the correct answer as one of the options)" />
                                            {data.options.map(
                                                (option, index) => (
                                                    <div key={index}>
                                                        <TextInput
                                                            value={option}
                                                            onChange={(e) =>
                                                                handleOptionChange(
                                                                    index,
                                                                    e.target
                                                                        .value
                                                                )
                                                            }
                                                            className="block w-full"
                                                            placeholder={`Option ${
                                                                index + 1
                                                            }`}
                                                        />
                                                    </div>
                                                )
                                            )}
                                            <InputError
                                                message={errors.options}
                                                className="mt-2"
                                            />
                                        </div>
                                    )}
                                </div>

                                {/* DepEd Competency */}
                                <div>
                                    <InputLabel
                                        htmlFor="deped_competency"
                                        value="DepEd Competency"
                                    />
                                    {availableCompetencies.length > 0 ? (
                                        <select
                                            id="deped_competency"
                                            value={data.deped_competency}
                                            onChange={(e) =>
                                                setData(
                                                    "deped_competency",
                                                    e.target.value
                                                )
                                            }
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        >
                                            <option value="">
                                                Select Competency
                                            </option>
                                            {availableCompetencies.map(
                                                (competency, index) => (
                                                    <option
                                                        key={index}
                                                        value={competency}
                                                    >
                                                        {competency}
                                                    </option>
                                                )
                                            )}
                                        </select>
                                    ) : (
                                        <TextInput
                                            id="deped_competency"
                                            value={data.deped_competency}
                                            onChange={(e) =>
                                                setData(
                                                    "deped_competency",
                                                    e.target.value
                                                )
                                            }
                                            className="mt-1 block w-full"
                                            placeholder="Enter DepEd competency..."
                                            required
                                        />
                                    )}
                                    <InputError
                                        message={errors.deped_competency}
                                        className="mt-2"
                                    />
                                    {data.grade_level &&
                                        data.question_type &&
                                        availableCompetencies.length === 0 && (
                                            <p className="mt-1 text-sm text-gray-500">
                                                No predefined competencies
                                                available. Please enter
                                                manually.
                                            </p>
                                        )}
                                </div>

                                {/* Submit Buttons */}
                                <div className="flex items-center justify-end space-x-3">
                                    <Link
                                        href={route("teacher.questions.index")}
                                    >
                                        <SecondaryButton type="button">
                                            Cancel
                                        </SecondaryButton>
                                    </Link>
                                    <PrimaryButton disabled={processing}>
                                        {processing
                                            ? "Updating..."
                                            : "Update Question"}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
