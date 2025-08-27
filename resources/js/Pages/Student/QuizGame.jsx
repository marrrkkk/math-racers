import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import RaceTrack from "@/Components/RaceTrack";
import soundEffects from "@/utils/soundEffects";
import { Head, router } from "@inertiajs/react";
import { useState, useEffect, useRef } from "react";

export default function QuizGame({
    user,
    grade,
    topic,
    questions,
    sessionId,
    totalQuestions = 10,
    answer_result = null,
}) {
    const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
    const [userAnswer, setUserAnswer] = useState("");
    const [correctAnswers, setCorrectAnswers] = useState(0);
    const [timeLeft, setTimeLeft] = useState(300); // 5 minutes total
    const [questionStartTime, setQuestionStartTime] = useState(Date.now());
    const [isAnswering, setIsAnswering] = useState(false);
    const [showFeedback, setShowFeedback] = useState(false);
    const [lastAnswerCorrect, setLastAnswerCorrect] = useState(false);
    const [racerPosition, setRacerPosition] = useState(0);
    const [gameCompleted, setGameCompleted] = useState(false);
    const [finalScore, setFinalScore] = useState(null);

    const timerRef = useRef(null);
    const answerInputRef = useRef(null);

    const currentQuestion = questions[currentQuestionIndex];
    const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
    const racerProgress = (correctAnswers / totalQuestions) * 100;

    // Timer effect
    useEffect(() => {
        if (timeLeft > 0 && !gameCompleted) {
            timerRef.current = setTimeout(() => {
                setTimeLeft(timeLeft - 1);
            }, 1000);
        } else if (timeLeft === 0 && !gameCompleted) {
            handleQuizComplete();
        }

        return () => {
            if (timerRef.current) {
                clearTimeout(timerRef.current);
            }
        };
    }, [timeLeft, gameCompleted]);

    // Focus input when question changes
    useEffect(() => {
        if (answerInputRef.current && !showFeedback) {
            answerInputRef.current.focus();
        }
    }, [currentQuestionIndex, showFeedback]);

    // Keyboard shortcuts
    useEffect(() => {
        const handleKeyPress = (e) => {
            if (
                e.key === "Enter" &&
                !isAnswering &&
                userAnswer.trim() &&
                !showFeedback
            ) {
                handleAnswerSubmit(e);
            }
        };

        document.addEventListener("keypress", handleKeyPress);
        return () => document.removeEventListener("keypress", handleKeyPress);
    }, [isAnswering, userAnswer, showFeedback]);

    const formatTime = (seconds) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, "0")}`;
    };

    const handleAnswerSubmit = async (e) => {
        e.preventDefault();
        if (isAnswering || !userAnswer.trim()) return;

        setIsAnswering(true);
        const timeTaken = Math.floor((Date.now() - questionStartTime) / 1000);
        const isCorrect =
            currentQuestion.correct_answer.toString() === userAnswer.trim();

        // Update UI optimistically
        setLastAnswerCorrect(isCorrect);
        setShowFeedback(true);

        // Play sound effects
        if (isCorrect) {
            soundEffects.playSuccess();
            soundEffects.playRacerMove();
            setTimeout(() => soundEffects.playSparkle(), 200);
            setCorrectAnswers((prev) => prev + 1);
            setRacerPosition((prev) =>
                Math.min(prev + 100 / totalQuestions, 100)
            );
        } else {
            soundEffects.playError();
        }

        // Show feedback for 2 seconds then move to next question
        setTimeout(() => {
            setShowFeedback(false);
            setUserAnswer("");
            setIsAnswering(false);

            if (currentQuestionIndex + 1 >= totalQuestions) {
                handleQuizComplete();
            } else {
                setCurrentQuestionIndex((prev) => prev + 1);
                setQuestionStartTime(Date.now());
            }
        }, 2000);

        // Submit answer to backend (fire and forget)
        try {
            await router.post(
                route("student.quiz.answer", { session: sessionId }),
                {
                    question_id: currentQuestion.id,
                    answer: userAnswer.trim(),
                    time_taken: timeTaken,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    onError: (errors) => {
                        console.error("Error submitting answer:", errors);
                    },
                }
            );
        } catch (error) {
            console.error("Error submitting answer:", error);
        }
    };

    const handleQuizComplete = async () => {
        if (gameCompleted) return;

        setGameCompleted(true);
        soundEffects.playCompletion();
        // Add power-up sound for game completion
        setTimeout(() => soundEffects.playPowerUp(), 1000);

        try {
            await router.post(
                route("student.quiz.complete", { session: sessionId }),
                {
                    total_time: 300 - timeLeft,
                },
                {
                    preserveState: true,
                    onSuccess: (page) => {
                        setFinalScore(page.props.finalScore);
                    },
                    onError: (errors) => {
                        console.error("Error completing quiz:", errors);
                    },
                }
            );
        } catch (error) {
            console.error("Error completing quiz:", error);
        }
    };

    const getTopicConfig = (topic) => {
        const configs = {
            addition: {
                icon: "➕",
                color: "text-green-600",
                bg: "bg-green-100",
            },
            subtraction: {
                icon: "➖",
                color: "text-red-600",
                bg: "bg-red-100",
            },
            multiplication: {
                icon: "✖️",
                color: "text-blue-600",
                bg: "bg-blue-100",
            },
            division: {
                icon: "➗",
                color: "text-purple-600",
                bg: "bg-purple-100",
            },
        };
        return configs[topic] || configs.addition;
    };

    const topicConfig = getTopicConfig(topic);

    if (gameCompleted && finalScore) {
        return (
            <AuthenticatedLayout
                header={
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        🏁 Quiz Complete!
                    </h2>
                }
            >
                <Head title="Quiz Complete" />

                <div className="py-12">
                    <div className="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                        <div className="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl p-8 text-center text-white shadow-xl">
                            <div className="text-6xl mb-4">🏆</div>
                            <h1 className="text-4xl font-bold mb-4">
                                Race Complete!
                            </h1>
                            <p className="text-xl mb-6">
                                Great job, {user.name}!
                            </p>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                                <div className="bg-white bg-opacity-20 rounded-lg p-4">
                                    <div className="text-3xl font-bold">
                                        {correctAnswers}/{totalQuestions}
                                    </div>
                                    <div className="text-sm">
                                        Correct Answers
                                    </div>
                                </div>
                                <div className="bg-white bg-opacity-20 rounded-lg p-4">
                                    <div className="text-3xl font-bold">
                                        {finalScore.points}
                                    </div>
                                    <div className="text-sm">Points Earned</div>
                                </div>
                                <div className="bg-white bg-opacity-20 rounded-lg p-4">
                                    <div className="text-3xl font-bold">
                                        {Math.round(finalScore.accuracy)}%
                                    </div>
                                    <div className="text-sm">Accuracy</div>
                                </div>
                            </div>

                            <div className="flex flex-col sm:flex-row gap-4 justify-center">
                                <button
                                    onClick={() =>
                                        router.get(
                                            route("student.topics", { grade })
                                        )
                                    }
                                    className="bg-white text-orange-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors"
                                >
                                    Play Again 🎮
                                </button>
                                <button
                                    onClick={() =>
                                        router.get(route("student.dashboard"))
                                    }
                                    className="bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-orange-700 transition-colors border-2 border-white"
                                >
                                    Back to Dashboard 🏠
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        🏁 {topicConfig.icon} Grade {grade}{" "}
                        {topic.charAt(0).toUpperCase() + topic.slice(1)} Race
                    </h2>
                    <div className="text-sm text-gray-600">
                        Question {currentQuestionIndex + 1} of {totalQuestions}
                    </div>
                </div>
            }
        >
            <Head title={`${topic} Quiz - Grade ${grade}`} />

            <div className="py-6">
                <div className="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    {/* Timer and Progress Bar */}
                    <div className="mb-6 bg-white rounded-xl shadow-lg p-4">
                        <div className="flex items-center justify-between mb-4">
                            <div className="flex items-center space-x-4">
                                <div className="text-2xl">⏰</div>
                                <div>
                                    <div className="text-lg font-semibold">
                                        {formatTime(timeLeft)}
                                    </div>
                                    <div className="text-sm text-gray-500">
                                        Time Remaining
                                    </div>
                                </div>
                            </div>
                            <div className="flex items-center space-x-4">
                                <button
                                    onClick={() => soundEffects.toggle()}
                                    className="text-2xl hover:scale-110 transition-transform"
                                    title={
                                        soundEffects.isEnabled()
                                            ? "Disable Sound"
                                            : "Enable Sound"
                                    }
                                >
                                    {soundEffects.isEnabled() ? "🔊" : "🔇"}
                                </button>
                                <div className="text-right">
                                    <div className="text-lg font-semibold">
                                        {correctAnswers} / {totalQuestions}
                                    </div>
                                    <div className="text-sm text-gray-500">
                                        Correct Answers
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Progress Bar */}
                        <div className="w-full bg-gray-200 rounded-full h-3">
                            <div
                                className="bg-blue-600 h-3 rounded-full transition-all duration-500"
                                style={{ width: `${progress}%` }}
                            ></div>
                        </div>
                    </div>

                    {/* Race Track */}
                    <RaceTrack
                        racerProgress={racerProgress}
                        isAnimating={lastAnswerCorrect && showFeedback}
                        showCelebration={
                            gameCompleted && correctAnswers === totalQuestions
                        }
                        className="mb-6"
                    />

                    {/* Question Card */}
                    <div
                        className={`bg-white rounded-xl shadow-xl p-8 mb-6 transition-all duration-500 ${
                            showFeedback
                                ? lastAnswerCorrect
                                    ? "glow-success racing-card-enhanced"
                                    : "glow-error shake-error"
                                : "shadow-playful"
                        }`}
                    >
                        {showFeedback ? (
                            <div className="text-center relative">
                                {/* Celebration particles for correct answers */}
                                {lastAnswerCorrect && (
                                    <div className="absolute inset-0 pointer-events-none">
                                        <div
                                            className="absolute top-4 left-4 text-2xl animate-bounce"
                                            style={{ animationDelay: "0.1s" }}
                                        >
                                            ⭐
                                        </div>
                                        <div
                                            className="absolute top-8 right-8 text-2xl animate-bounce"
                                            style={{ animationDelay: "0.3s" }}
                                        >
                                            ✨
                                        </div>
                                        <div
                                            className="absolute bottom-12 left-8 text-2xl animate-bounce"
                                            style={{ animationDelay: "0.5s" }}
                                        >
                                            🌟
                                        </div>
                                        <div
                                            className="absolute bottom-8 right-4 text-2xl animate-bounce"
                                            style={{ animationDelay: "0.7s" }}
                                        >
                                            💫
                                        </div>
                                    </div>
                                )}

                                <div
                                    className={`text-6xl mb-4 ${
                                        lastAnswerCorrect
                                            ? "celebrate-bounce sparkle"
                                            : "shake-error"
                                    }`}
                                >
                                    {lastAnswerCorrect ? "🎉" : "😔"}
                                </div>
                                <h3
                                    className={`text-3xl font-bold mb-2 ${
                                        lastAnswerCorrect
                                            ? "text-green-600 celebrate-pulse"
                                            : "text-red-600"
                                    }`}
                                >
                                    {lastAnswerCorrect
                                        ? "Awesome! 🏆"
                                        : "Oops! Try again! 💪"}
                                </h3>
                                <p
                                    className={`text-lg mb-4 ${
                                        lastAnswerCorrect
                                            ? "text-green-700 font-semibold"
                                            : "text-red-700"
                                    }`}
                                >
                                    {lastAnswerCorrect
                                        ? "Your racer zooms forward! 🏎️💨"
                                        : `The correct answer was: ${currentQuestion.correct_answer}`}
                                </p>

                                {/* Motivational messages */}
                                <div
                                    className={`text-sm px-4 py-2 rounded-full inline-block ${
                                        lastAnswerCorrect
                                            ? "bg-green-100 text-green-800"
                                            : "bg-blue-100 text-blue-800"
                                    }`}
                                >
                                    {lastAnswerCorrect
                                        ? "🚀 Keep up the great work!"
                                        : "🌟 You'll get the next one!"}
                                </div>
                            </div>
                        ) : (
                            <div>
                                <div className="text-center mb-6">
                                    <div
                                        className={`inline-flex items-center px-4 py-2 rounded-full ${topicConfig.bg} ${topicConfig.color} font-semibold mb-4`}
                                    >
                                        <span className="mr-2">
                                            {topicConfig.icon}
                                        </span>
                                        Question {currentQuestionIndex + 1}
                                    </div>
                                </div>

                                <div className="text-center mb-8">
                                    <h2 className="text-4xl font-bold text-gray-800 mb-4">
                                        {currentQuestion.question_text}
                                    </h2>
                                </div>

                                <form
                                    onSubmit={handleAnswerSubmit}
                                    className="max-w-md mx-auto"
                                >
                                    <div className="mb-6">
                                        <div className="relative">
                                            <input
                                                ref={answerInputRef}
                                                type="text"
                                                value={userAnswer}
                                                onChange={(e) =>
                                                    setUserAnswer(
                                                        e.target.value
                                                    )
                                                }
                                                className={`w-full text-center text-3xl font-bold py-4 px-6 border-4 rounded-xl focus:outline-none transition-all duration-300 transform ${
                                                    userAnswer.trim()
                                                        ? "border-green-400 shadow-playful scale-105 bg-green-50"
                                                        : "border-gray-300 hover:border-blue-400 focus:border-blue-500"
                                                } ${
                                                    isAnswering
                                                        ? "animate-pulse"
                                                        : ""
                                                }`}
                                                placeholder="Type your answer..."
                                                disabled={isAnswering}
                                                autoComplete="off"
                                            />
                                            {/* Floating emoji indicators */}
                                            {userAnswer.trim() && (
                                                <div className="absolute -top-2 -right-2 text-2xl animate-bounce">
                                                    ✨
                                                </div>
                                            )}
                                        </div>
                                        <div className="text-center mt-3 text-sm text-gray-500 flex items-center justify-center">
                                            <span className="mr-2">⌨️</span>
                                            Press Enter to submit your answer
                                            <span className="ml-2">🚀</span>
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={
                                            isAnswering || !userAnswer.trim()
                                        }
                                        className={`btn-playful w-full text-xl font-bold py-4 px-8 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 ${
                                            userAnswer.trim() && !isAnswering
                                                ? "shadow-playful-lg transform hover:scale-105"
                                                : ""
                                        }`}
                                    >
                                        {isAnswering ? (
                                            <span className="flex items-center justify-center">
                                                <svg
                                                    className="animate-spin -ml-1 mr-3 h-6 w-6 text-white"
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
                                                <span className="animate-pulse">
                                                    Racing to finish line...
                                                </span>
                                            </span>
                                        ) : (
                                            <span className="flex items-center justify-center">
                                                <span className="mr-2">🏁</span>
                                                Submit Answer
                                                <span className="ml-2">🚀</span>
                                            </span>
                                        )}
                                    </button>
                                </form>
                            </div>
                        )}
                    </div>

                    {/* Racing Tips */}
                    <div className="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-4">
                        <div className="flex items-center justify-center space-x-6 text-sm text-gray-700">
                            <div className="flex items-center">
                                <span className="mr-2">⚡</span>
                                <span>Answer quickly for bonus points!</span>
                            </div>
                            <div className="flex items-center">
                                <span className="mr-2">🎯</span>
                                <span>
                                    Correct answers move your racer forward!
                                </span>
                            </div>
                            <div className="flex items-center">
                                <span className="mr-2">🏆</span>
                                <span>Reach the finish line to win!</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
