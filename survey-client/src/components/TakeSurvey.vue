<script>
import {postData, getData} from "../utility/Utility"
import AnswerQuestion from './AnswerQuestion.vue';

export default {
    components: {
        AnswerQuestion,
    },
    mounted() {
        this.startSurvey()
    },
    // Dodanie property wybranego optiona do question - wtedy mozna recznie stworzyc answer group w submicie forma
    data() {
        return {
            survey: {},
            questions: [],
            answersSubmitted: false,
            error: '',
            answers: {},
        }
    },
    methods: {
        submitSurvey() {
            postData(`http://localhost:8000/api/survey/send-answers/${this.$route.params.id}`, this.answers, 'POST')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.answersSubmitted = true;
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        startSurvey() {
            // Get the survey (for displaying title)
            getData(`http://localhost:8000/api/survey/${this.$route.params.id}`)
            .then((data) => {
                if (data.error !== undefined && data.error != '') {
                    this.error = data.error
                    return
                }
                this.survey = data
            });
            // Get questions for given survey
            getData(`http://localhost:8000/api/survey/list-questions/${this.$route.params.id}`)
            .then((data) => {
                this.questions = data
            });
        },
        optionPicked(answerId, optionId) {
            this.answers[answerId] = optionId;
        },
    },
}
</script>

<template>
    <p v-if="error">{{ error }}</p>
    <p v-if="answersSubmitted">Your answers were succesfully submitted!</p>
    <div v-show="!answersSubmitted">
        <AnswerQuestion
            v-for="question in questions"
            :key="question.id"
            :answerData="{id: question.id, title: question.title, description: question.description}"
            @optionPicked="optionPicked"
        />
        <button type="submit" @click="submitSurvey">Submit answers</button>
    </div>
</template>

<style></style>