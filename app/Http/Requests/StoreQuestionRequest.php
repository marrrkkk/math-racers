<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return $this->user()->role->value === 'teacher';
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'question_text' => 'required|string|max:1000',
      'question_type' => 'required|in:addition,subtraction,multiplication,division',
      'grade_level' => 'required|integer|between:1,3',
      'difficulty' => 'required|in:easy,medium,hard',
      'correct_answer' => 'required|string|max:255',
      'options' => 'nullable|array|max:4',
      'options.*' => 'string|max:255',
      'deped_competency' => 'required|string|max:255',
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'question_text.required' => 'The question text is required.',
      'question_text.max' => 'The question text may not be greater than 1000 characters.',
      'question_type.required' => 'Please select a question type.',
      'question_type.in' => 'The selected question type is invalid.',
      'grade_level.required' => 'Please select a grade level.',
      'grade_level.between' => 'Grade level must be between 1 and 3.',
      'difficulty.required' => 'Please select a difficulty level.',
      'difficulty.in' => 'The selected difficulty level is invalid.',
      'correct_answer.required' => 'The correct answer is required.',
      'correct_answer.max' => 'The correct answer may not be greater than 255 characters.',
      'options.max' => 'You may not have more than 4 answer options.',
      'options.*.max' => 'Each answer option may not be greater than 255 characters.',
      'deped_competency.required' => 'The DepEd competency is required.',
      'deped_competency.max' => 'The DepEd competency may not be greater than 255 characters.',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   */
  public function attributes(): array
  {
    return [
      'question_text' => 'question text',
      'question_type' => 'question type',
      'grade_level' => 'grade level',
      'difficulty' => 'difficulty level',
      'correct_answer' => 'correct answer',
      'options' => 'answer options',
      'deped_competency' => 'DepEd competency',
    ];
  }
}
