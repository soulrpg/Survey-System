<script>
import AnswerQuestion from './AnswerQuestion.vue';

async function postData(url, data = {}) {
    const response = await fetch(url, {
        method: 'POST',
        cache: 'no-cache',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data, null, "\t")
    });
    return await response.json();
}

async function getData(url) {
    const response = await fetch(url, {
        method: 'GET',
        cache: 'no-cache',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + getCookie('surveyUserToken')
        }
    });
    return await response.json();
}

export default {
    components: {
        AnswerQuestion,
    },
    mounted() {
        startSurvey()
    },
    // Dodanie property wybranego optiona do question - wtedy mozna recznie stworzyc answer group w submicie forma
    data() {
        return {
            survey: {},
            questions: [],
            answersSubmitted: false,
        }
    },
    submitSurvey() {
        answers = [];
        this.questions.forEach((question) => {
            answers.push({pickedOption: question.answer})
        });
        postData(`http://localhost:8000/api/survey/survey_send_answers/${route.params.id}`, answers)
        .then((data) => {
            if (data.msg !== undefined && data.msg === 'Success') {
                answersSubmitted = true
            } else {
                this.error = data.message ?? '';
            }
        });
    },
    startSurvey() {
        // Get the survey (for displaying title)
        getData(`http://localhost:8000/api/survey/${route.params.id}`)
        .then((data) => {
            this.survey = data
        });
        // Get questions for given survey
        getData(`http://localhost:8000/api/survey/list-questions/${route.params.id}`)
        .then((data) => {
            this.questions = data
            this.questions.forEach((question) => {
                question.answer = 0
            });
        });
    },
}
</script>

<template>
    <div v-show="!answersSubmitted">
        <AnswerQuestion
            v-for="question in questions"
            :key="question.id"
            :surveyData="{id: question.id, title: question.title, description: question.description, answer: question.answer}"
        />
    </div>
</template>

<style></style>