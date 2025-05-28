<template>
  <v-container class="pa-4">
    <v-card class="h-96 overflow-y-auto pa-4" elevation="0" variant="outlined">
      <v-card-title class="text-h6 mb-4">
        Chat Room built with Laravel Echo and Reverb
      </v-card-title>
      <v-card-subtitle class="text-caption mb-2">
        You are: {{ username }}
      </v-card-subtitle>
      <v-card-text>
        <div v-for="msg in messages" :key="msg.id" class="mb-2 d-flex"
          :class="msg.username === username ? 'justify-end' : 'justify-start'">
          <v-sheet width="80%" :color="msg.username === username ? 'blue-lighten-4' : 'grey-lighten-4'"
            :class="msg.username === username ? 'text-right' : 'text-left'"
            class="pa-2 rounded-lg">
            <div class="text-caption text-grey-darken-1 mb-1">
              {{ msg.username === username ? 'You' : msg.username }}
            </div>
            <div>{{ msg.body }}</div>
          </v-sheet>
        </div>
      </v-card-text>
    </v-card>

    <v-row class="mt-4" align="center">
      <v-col cols="10">
        <v-text-field v-model="newMessage" @keyup.enter="sendMessage" label="Send a message" density="compact"
          variant="outlined" hide-details />
      </v-col>
      <v-col cols="2">
        <v-btn @click="sendMessage" color="primary" variant="flat" block>Send</v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const Echo = window.Echo
const chatId = 1
const messages = ref([])
const newMessage = ref('')
const username = ref('')

function generateUsername() {
  const names = ['Einstein', 'Newton', 'Galileu', 'Curie', 'Tesla', 'DaVinci', 'Archimedes']
  return names[Math.floor(Math.random() * names.length)]
}

onMounted(async () => {
  username.value = localStorage.getItem('anon_name') || generateUsername()
  localStorage.setItem('anon_name', username.value)

  const { data } = await axios.get(`/api/chats/${chatId}/messages`)
  messages.value = data

  Echo.channel(`chat.${chatId}`).listen('MessageSent', (e) => {
    messages.value.push(e.message)
  })
})

async function sendMessage() {
  if (!newMessage.value.trim()) return

  await axios.post('/api/messages', {
    chat_id: chatId,
    username: username.value,
    body: newMessage.value,
  })

  newMessage.value = ''
}
</script>
