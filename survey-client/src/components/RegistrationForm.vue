<script>
import {postData} from "../utility/Utility"

export default {
    data() {
        return {
            userData: {
                email: '',
                password: {
                    first: '',
                    second: '',
                },
            },
            msg: '',
            error: '',
        }
    },
    methods: {
        register() {
            postData('http://localhost:8000/api/register', this.userData)
            .then((data) => {
                this.error = data.errors ?? '';
                if (data.msg === 'success') {
                    this.userData.email = '';
                    this.password.first = '';
                    this.password.second = '';
                    this.msg = 'Registration successful. You can login now.'
                }
            });
        },
    }
}
</script>

<template>
    <h2>Register</h2>
    <p v-if="msg">{{msg}}</p>
    <p v-if="error">{{error}}</p>
    <form @submit.prevent="register" action="/" method="post">
        <label for="email">Email:</label><br/>
        <input type="text" id="email" v-model="userData.email"><br/>
        <label for="password">Password:</label><br/>
        <input type="password" id="password" v-model="userData.password.first"/><br/>
        <label for="repeat-password">Repeat password:</label><br/>
        <input type="password" id="repeat-password" v-model="userData.password.second"/><br/>
        <input type="submit" value="Register"/>
    </form>
</template>

<style></style>