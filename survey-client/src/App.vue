<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <header>
    <nav>
      <RouterLink to="/">Home</RouterLink>
      <RouterLink to="/login">Login</RouterLink>
      <RouterLink to="/survey/1">Take a survey</RouterLink>
      <RouterLink to="/edit">Edit surveys</RouterLink>
    </nav>
  </header>

  <RouterView />
</template>

<style scoped>

</style>
