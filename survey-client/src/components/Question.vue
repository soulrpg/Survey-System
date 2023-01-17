<script>
import Option from './Option.vue';
import {postData, getData} from "../utility/Utility"

export default {
    props: ['questionData', 'index'],
    components: {
        Option,
    },
    data() {
        return {
            optionsVisible: false,
            options: [],
            newOption: {
                name: '',
                question: this.index,
            },
            error: '',
        }
    },
    methods: {
        updateQuestion() {
            postData(`http://localhost:8000/api/question/update/${this.index}`, this.questionData, 'PUT')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateQuestionList();
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        deleteQuestion() {
            postData(`http://localhost:8000/api/question/delete/${this.index}`, {}, 'DELETE')
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.$parent.updateQuestionList()
                } else {
                    this.error = data.msg ?? '';
                }
            });
        },
        addNewOption() {
            postData('http://localhost:8000/api/option/create', this.newOption)
            .then((data) => {
                if (data.msg !== undefined && data.msg === 'Success') {
                    this.updateOptionList()
                } else {
                    this.error = data.message ?? '';
                }
            });
        },
        changeOptionsVisibility() {
            this.optionsVisible = !this.optionsVisible;
            if (this.optionsVisible) {
                this.updateOptionList()
            }
        },
        updateOptionList() {
            getData(`http://localhost:8000/api/question/list-options/${this.index}`)
            .then((data) => {
                this.options = data
            });
        }
    }
}
</script>

<template>
    <h2>{{ questionData.title }}</h2>
    <p v-if="error">{{error}}</p>
    <form @submit.prevent="updateQuestion" action="/" method="post">
        <label for="title">Title:</label><br/>
        <input type="text" id="title" v-model="questionData.title"><br/>
        <label for="description">Description:</label><br/>
        <textarea rows="3" cols="50" id="description" v-model="questionData.description"/><br/>
        <input type="hidden" id="identifier" v-model="questionData.survey"/>
        <input type="submit" value="Update Question"/>
    </form>
    <form @submit.prevent="deleteQuestion" action="/" method="post">
        <input type="hidden" id="identifier" v-model="questionData.survey"/>
        <input type="submit" value="Delete Question"/>
    </form>
    <button @click="changeOptionsVisibility">Show options</button>
    <div v-show="optionsVisible">
        <Option
            v-for="option in options"
            :key="option.id"
            :index="option.id"
            :optionData="{question: index, name: option.name}"
        />
        <h3>Add new option</h3>
        <form @submit.prevent="addNewOption" action="/" method="post">
            <label for="name">Name:</label><br/>
            <input type="text" id="name" v-model="newOption.name"><br/>
            <input type="hidden" id="identifier" v-model="newOption.question"/>
            <input type="submit" value="Create New Option"/>
        </form>
    </div>
</template>

<style></style>