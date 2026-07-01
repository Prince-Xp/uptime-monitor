<template>
  <div class="container">
    <h1>Website Uptime Monitor</h1>

    <svg class="pulse-line" viewBox="0 0 300 24" preserveAspectRatio="none">
      <path d="M0,12 L110,12 L120,4 L130,20 L140,2 L150,22 L160,12 L300,12" />
    </svg>

    <label for="client-select">Client</label>
    <select id="client-select" v-model="selectedClientId" @change="loadWebsites">
      <option disabled value="">Select a client…</option>
      <option v-for="client in clients" :key="client.id" :value="client.id">
        {{ client.email }}
      </option>
    </select>

    <p class="empty-state" v-if="loadingWebsites">Loading websites…</p>
    <p class="empty-state" v-else-if="selectedClientId && websites.length === 0">
      This client has no monitored websites yet.
    </p>

    <ul v-if="websites.length">
      <li v-for="site in websites" :key="site.id">
        <span class="status-dot" :class="site.is_up ? 'status-up' : 'status-down'"></span>
        <a href="#" @click.prevent="confirmVisit(site.url)">{{ site.url }}</a>
        <span class="status-label">{{ site.is_up ? 'Up' : 'Down' }}</span>
      </li>
    </ul>

    <div v-if="pendingUrl" class="dialog-backdrop" @click.self="cancelVisit">
      <div class="dialog" role="dialog" aria-modal="true">
        <p>You are about to visit <code>{{ pendingUrl }}</code>. Do you want to continue?</p>
        <div class="dialog-actions">
          <button @click="cancelVisit">Cancel</button>
          <button @click="continueVisit" class="primary">Continue</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'App',
  data() {
    return {
      clients: [],
      websites: [],
      selectedClientId: '',
      loadingWebsites: false,
      pendingUrl: null,
    };
  },
  mounted() {
    this.loadClients();
  },
  methods: {
    async loadClients() {
      const { data } = await axios.get('/api/clients');
      this.clients = data;
    },
    async loadWebsites() {
      this.websites = [];
      if (!this.selectedClientId) return;
      this.loadingWebsites = true;
      try {
        const { data } = await axios.get(`/api/clients/${this.selectedClientId}/websites`);
        this.websites = data;
      } finally {
        this.loadingWebsites = false;
      }
    },
    confirmVisit(url) {
      this.pendingUrl = url;
    },
    cancelVisit() {
      this.pendingUrl = null;
    },
    continueVisit() {
      window.open(this.pendingUrl, '_blank', 'noopener,noreferrer');
      this.pendingUrl = null;
    },
  },
};
</script>