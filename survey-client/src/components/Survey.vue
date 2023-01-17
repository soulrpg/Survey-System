<script>
import Question from './Question.vue';
import {postData, getData} from "../utility/Utility"

export default {
    props: ['surveyData'],
    components: {
        Question,
    },
    data() {
        return {
            questionsVisible: false,
            answersVisible: false,
            questions: [],
            newQuestion: {
                title: '',
                survey: this.surveyData.id,
                descrption: '',
            },
            answers: {},
            error: '',
        }
    },
    methods: {
        updateSurvey() {
            postData(`http://localhost:8000/api/survey/update/${this.surveyData.id}`, this.surveyData, 'PUT')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateSurveyList();
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        deleteSurvey() {
            postData(`http://localhost:8000/api/survey/delete/${this.surveyData.id}`, {}, 'DELETE')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateSurveyList()
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        addNewQuestion() {
            postData('http://localhost:8000/api/question/create', this.newQuestion)
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.updateQuestionList()
                } else {
                    this.error = data.message ?? '';
                }
            });
        },
        changeQuestionsVisibility() {
            this.questionsVisible = !this.questionsVisible;
            if (this.questionsVisible) {
                this.answersVisible = false;
                this.updateQuestionList()
            }
        },
        updateQuestionList() {
            getData(`http://localhost:8000/api/survey/list-questions/${this.surveyData.id}`)
            .then((data) => {
                this.questions = data
            });
        },
        changeAnswersVisibility() {
            this.answersVisible = !this.answersVisible;
            if (this.answersVisible) {
                this.questionsVisible = false;
                this.updateAnswerList()
            }  
        },
        updateAnswerList() {
            getData(`http://localhost:8000/api/survey/answer-count/${this.surveyData.id}`)
            .then((data) => {
                this.answers = data
            });
        }
    }
}
</script>

<template>
    <h2>{{ surveyData.title }}</h2>
    <h3>Id: {{ surveyData.id }}</h3>
    <p v-if="error">{{error}}</p>
    <form @submit.prevent="updateSurvey" action="/" method="post">
        <label for="title">Title:</label><br/>
        <input type="text" id="title" v-model="surveyData.title"><br/>
        <label for="public">Is Public:</label><br/>
        <input type="checkbox" id="public" v-model="surveyData.public"/><br/>
        <label for="description">Description:</label><br/>
        <textarea rows="3" cols="50" id="description" v-model="surveyData.description"/><br/>
        <input type="hidden" id="identifier" v-model="surveyData.id"/>
        <input type="submit" value="Update Survey"/>
    </form>
    <form @submit.prevent="deleteSurvey" action="/" method="post">
        <input type="hidden" id="identifier" v-model="surveyData.id"/>
        <input type="submit" value="Delete Survey"/>
    </form>
    <button @click="changeQuestionsVisibility">Show questions</button>
    <button @click="changeAnswersVisibility">Show answers</button>
    <div v-show="questionsVisible">
        <Question
            v-for="question in questions"
            :key="question.id"
            :index="question.id"
            :questionData="{survey: surveyData.id, title: question.title, description: question.description}"
        />
        <h3>Add new question</h3>
        <form @submit.prevent="addNewQuestion" action="/" method="post">
            <label for="title">Title:</label><br/>
            <input type="text" id="title" v-model="newQuestion.title"><br/>
            <label for="description">Description:</label><br/>
            <textarea rows="3" cols="50" id="description" v-model="newQuestion.description"/><br/>
            <input type="hidden" id="identifier" v-model="newQuestion.survey"/>
            <input type="submit" value="Create New Question"/>
        </form>
    </div>
    <div v-show="answersVisible">
        <div v-for="(answer, name) in answers">
            <h5>{{ name }}</h5>
            <ol>
                <li v-for="(count, option) in answer">
                    {{ option }}: {{ count }}
                </li>
            </ol>
        </div>
    </div>
</template>

<style></style>