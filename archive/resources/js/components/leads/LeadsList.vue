<template>
  <div class="leads-container">
    <!-- Header with title and action buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0">Leads</h2>
      <div>
        <button @click="showImportModal = true" class="btn btn-outline-secondary me-2">
          <i class="fas fa-upload me-1"></i> Import
        </button>
        <button @click="exportLeads" class="btn btn-outline-primary me-2">
          <i class="fas fa-download me-1"></i> Export
        </button>
        <button @click="showCreateModal = true" class="btn btn-primary">
          <i class="fas fa-plus me-1"></i> Add Lead
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <v-select 
              v-model="filters.status_id" 
              :options="statuses" 
              label="name" 
              :reduce="status => status.id"
              placeholder="All Statuses"
              clearable
            ></v-select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Source</label>
            <v-select 
              v-model="filters.source_id" 
              :options="sources" 
              label="name" 
              :reduce="source => source.id"
              placeholder="All Sources"
              clearable
            ></v-select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Assigned To</label>
            <v-select 
              v-model="filters.assigned_to" 
              :options="users" 
              label="name" 
              :reduce="user => user.id"
              placeholder="All Users"
              clearable
            ></v-select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Tag</label>
            <v-select 
              v-model="filters.tag" 
              :options="tags" 
              label="name" 
              :reduce="tag => tag.name"
              placeholder="All Tags"
              clearable
              multiple
            ></v-select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date Range</label>
            <div class="input-group">
              <input 
                type="date" 
                class="form-control" 
                v-model="filters.start_date"
              >
              <span class="input-group-text">to</span>
              <input 
                type="date" 
                class="form-control" 
                v-model="filters.end_date"
              >
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label">Search</label>
            <div class="input-group">
              <input 
                type="text" 
                class="form-control" 
                v-model="filters.search" 
                placeholder="Search by name, email, company..."
                @keyup.enter="fetchLeads"
              >
              <button class="btn btn-outline-secondary" type="button" @click="fetchLeads">
                <i class="fas fa-search"></i>
              </button>
              <button class="btn btn-outline-secondary" type="button" @click="resetFilters">
                <i class="fas fa-undo"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Leads Table -->
    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-nowrap" style="width: 40px;">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    v-model="selectAll"
                    @change="toggleSelectAll"
                  >
                </th>
                <th class="text-nowrap" @click="sortBy('first_name')">
                  Name
                  <i class="fas" :class="getSortIcon('first_name')"></i>
                </th>
                <th class="text-nowrap" @click="sortBy('company')">
                  Company
                  <i class="fas" :class="getSortIcon('company')"></i>
                </th>
                <th class="text-nowrap">Contact</th>
                <th class="text-nowrap" @click="sortBy('status_id')">
                  Status
                  <i class="fas" :class="getSortIcon('status_id')"></i>
                </th>
                <th class="text-nowrap" @click="sortBy('source_id')">
                  Source
                  <i class="fas" :class="getSortIcon('source_id')"></i>
                </th>
                <th class="text-nowrap" @click="sortBy('assigned_to')">
                  Assigned To
                  <i class="fas" :class="getSortIcon('assigned_to')"></i>
                </th>
                <th class="text-nowrap" @click="sortBy('created_at')">
                  Created
                  <i class="fas" :class="getSortIcon('created_at')"></i>
                </th>
                <th class="text-nowrap text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="9" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="leads.length === 0">
                <td colspan="9" class="text-center py-4 text-muted">
                  No leads found. <a href="#" @click.prevent="resetFilters">Clear filters</a> to see all leads.
                </td>
              </tr>
              <tr 
                v-else 
                v-for="lead in leads" 
                :key="lead.id"
                :class="{ 'table-active': selectedLeads.includes(lead.id) }"
                @click="selectLead(lead.id, $event)"
                style="cursor: pointer;"
              >
                <td @click.stop>
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    :checked="selectedLeads.includes(lead.id)"
                    @change="toggleSelectLead(lead.id, $event)"
                  >
                </td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2">
                      <img 
                        :src="getAvatar(lead)" 
                        :alt="`${lead.first_name} ${lead.last_name}`"
                        class="rounded-circle"
                        width="32"
                        height="32"
                      >
                    </div>
                    <div>
                      <div class="fw-semibold">{{ lead.first_name }} {{ lead.last_name }}</div>
                      <div class="text-muted small">{{ lead.job_title || 'No title' }}</div>
                    </div>
                  </div>
                </td>
                <td>{{ lead.company || '—' }}</td>
                <td>
                  <div v-if="lead.email" class="text-truncate" style="max-width: 200px;" :title="lead.email">
                    <i class="fas fa-envelope me-1 text-muted"></i> {{ lead.email }}
                  </div>
                  <div v-if="lead.phone" class="text-truncate" style="max-width: 200px;" :title="lead.phone">
                    <i class="fas fa-phone me-1 text-muted"></i> {{ formatPhone(lead.phone) }}
                  </div>
                </td>
                <td>
                  <span 
                    class="badge" 
                    :style="{
                      backgroundColor: lead.status?.color || '#6c757d',
                      color: getContrastColor(lead.status?.color || '#6c757d')
                    }"
                  >
                    {{ lead.status?.name || '—' }}
                  </span>
                </td>
                <td>
                  <span v-if="lead.source" class="d-flex align-items-center">
                    <i :class="lead.source.icon || 'fas fa-question-circle me-1'" :style="{ color: lead.source.color || '#6c757d' }"></i>
                    {{ lead.source.name }}
                  </span>
                  <span v-else>—</span>
                </td>
                <td>
                  <div v-if="lead.assigned_to_user" class="d-flex align-items-center">
                    <img 
                      :src="lead.assigned_to_user.avatar || '/images/default-avatar.png'" 
                      :alt="lead.assigned_to_user.name"
                      class="rounded-circle me-2"
                      width="24"
                      height="24"
                    >
                    {{ lead.assigned_to_user.name }}
                  </div>
                  <span v-else>Unassigned</span>
                </td>
                <td class="text-nowrap">{{ formatDate(lead.created_at) }}</td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <button 
                      type="button" 
                      class="btn btn-link text-primary"
                      @click.stop="viewLead(lead.id)"
                      title="View"
                    >
                      <i class="fas fa-eye"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-link text-warning"
                      @click.stop="editLead(lead.id)"
                      title="Edit"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button 
                      type="button" 
                      class="btn btn-link text-danger"
                      @click.stop="confirmDelete(lead.id)"
                      title="Delete"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="leads.length > 0" class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted small">
          Showing {{ leads.length }} of {{ pagination.total }} leads
        </div>
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
              <button 
                class="page-link" 
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
              >
                Previous
              </button>
            </li>
            <li 
              v-for="page in visiblePages" 
              :key="page"
              class="page-item"
              :class="{ active: page === pagination.current_page }"
            >
              <button 
                class="page-link" 
                @click="changePage(page)"
              >
                {{ page }}
              </button>
            </li>
            <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
              <button 
                class="page-link" 
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
              >
                Next
              </button>
            </li>
          </ul>
        </nav>
        <div class="d-flex align-items-center">
          <label class="me-2 small text-muted">Per page:</label>
          <select 
            class="form-select form-select-sm" 
            style="width: 70px;"
            v-model="perPage"
            @change="changePerPage"
          >
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div 
      v-if="selectedLeads.length > 0" 
      class="bulk-actions-bar fixed-bottom bg-white shadow-lg p-3"
    >
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center">
            <div class="me-3">
              <span class="fw-semibold">{{ selectedLeads.length }}</span> selected
            </div>
            <div class="btn-group me-3">
              <button 
                type="button" 
                class="btn btn-outline-secondary btn-sm"
                @click="bulkChangeStatus"
              >
                <i class="fas fa-tag me-1"></i> Change Status
              </button>
              <button 
                type="button" 
                class="btn btn-outline-secondary btn-sm"
                @click="bulkAssign"
              >
                <i class="fas fa-user-plus me-1"></i> Assign To
              </button>
              <button 
                type="button" 
                class="btn btn-outline-secondary btn-sm"
                @click="bulkAddTag"
              >
                <i class="fas fa-tags me-1"></i> Add Tag
              </button>
            </div>
          </div>
          <div>
            <button 
              type="button" 
              class="btn btn-outline-danger btn-sm"
              @click="confirmBulkDelete"
            >
              <i class="fas fa-trash me-1"></i> Delete Selected
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Lead Modal -->
    <lead-form-modal
      :show="showCreateModal || showEditModal"
      :lead="currentLead"
      :statuses="statuses"
      :sources="sources"
      :users="users"
      :tags="tags"
      @saved="handleLeadSaved"
      @closed="closeModal"
    ></lead-form-modal>

    <!-- View Lead Modal -->
    <lead-details-modal
      v-if="currentLead"
      :show="showViewModal"
      :lead-id="currentLead.id"
      @closed="closeModal"
      @edit="editLead(currentLead.id)"
      @deleted="handleLeadDeleted"
    ></lead-details-modal>

    <!-- Bulk Status Change Modal -->
    <div class="modal fade" :class="{ 'show d-block': showBulkStatusModal }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Change Status</h5>
            <button type="button" class="btn-close" @click="showBulkStatusModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">New Status</label>
              <v-select 
                v-model="bulkStatusId" 
                :options="statuses" 
                label="name" 
                :reduce="status => status.id"
                placeholder="Select status"
              ></v-select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showBulkStatusModal = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="confirmBulkStatusChange" :disabled="!bulkStatusId">
              Update Status
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Assign Modal -->
    <div class="modal fade" :class="{ 'show d-block': showBulkAssignModal }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Assign Leads</h5>
            <button type="button" class="btn-close" @click="showBulkAssignModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Assign To</label>
              <v-select 
                v-model="bulkAssignTo" 
                :options="users" 
                label="name" 
                :reduce="user => user.id"
                placeholder="Select user"
              ></v-select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showBulkAssignModal = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="confirmBulkAssign" :disabled="!bulkAssignTo">
              Assign Leads
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Add Tag Modal -->
    <div class="modal fade" :class="{ 'show d-block': showBulkTagModal }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add Tag to Leads</h5>
            <button type="button" class="btn-close" @click="showBulkTagModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Tag Name</label>
              <v-select 
                v-model="bulkTagName" 
                :options="tags.map(tag => tag.name)" 
                taggable
                push-tags
                placeholder="Select or create a tag"
              ></v-select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showBulkTagModal = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="confirmBulkAddTag" :disabled="!bulkTagName">
              Add Tag
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" :class="{ 'show d-block': showDeleteModal }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirm Deletion</h5>
            <button type="button" class="btn-close" @click="showDeleteModal = false"></button>
          </div>
          <div class="modal-body">
            <p>Are you sure you want to delete the selected lead(s)? This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
            <button type="button" class="btn btn-danger" @click="deleteLead">
              <i class="fas fa-trash me-1"></i> Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Overlay for modals -->
    <div 
      v-if="showCreateModal || showEditModal || showViewModal || showBulkStatusModal || showBulkAssignModal || showBulkTagModal || showDeleteModal"
      class="modal-backdrop fade show"
      @click="closeModal"
    ></div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, computed, onMounted, watch } from 'vue';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';
import LeadFormModal from './LeadFormModal.vue';
import LeadDetailsModal from './LeadDetailsModal.vue';

export default {
  name: 'LeadsList',
  components: {
    LeadFormModal,
    LeadDetailsModal
  },
  setup() {
    const toast = useToast();
    const loading = ref(true);
    const leads = ref([]);
    const statuses = ref([]);
    const sources = ref([]);
    const users = ref([]);
    const tags = ref([]);
    const selectedLeads = ref([]);
    const selectAll = ref(false);
    const currentLead = ref(null);
    const showCreateModal = ref(false);
    const showEditModal = ref(false);
    const showViewModal = ref(false);
    const showDeleteModal = ref(false);
    const showBulkStatusModal = ref(false);
    const showBulkAssignModal = ref(false);
    const showBulkTagModal = ref(false);
    const bulkStatusId = ref(null);
    const bulkAssignTo = ref(null);
    const bulkTagName = ref('');
    const leadToDelete = ref(null);
    const sortField = ref('created_at');
    const sortDirection = ref('desc');
    const perPage = ref(10);
    const currentPage = ref(1);
    const pagination = ref({
      total: 0,
      per_page: 10,
      current_page: 1,
      last_page: 1,
    });

    const filters = ref({
      status_id: null,
      source_id: null,
      assigned_to: null,
      tag: null,
      search: '',
      start_date: '',
      end_date: ''
    });

    // Computed properties
    const visiblePages = computed(() => {
      const pages = [];
      const maxVisible = 5;
      let startPage = Math.max(1, pagination.value.current_page - Math.floor(maxVisible / 2));
      const endPage = Math.min(pagination.value.last_page, startPage + maxVisible - 1);
      
      if (endPage - startPage + 1 < maxVisible) {
        startPage = Math.max(1, endPage - maxVisible + 1);
      }
      
      for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
      }
      
      return pages;
    });

    // Methods
    const fetchLeads = async () => {
      try {
        loading.value = true;
        const params = {
          page: currentPage.value,
          per_page: perPage.value,
          sort_by: sortField.value,
          sort_dir: sortDirection.value,
          ...Object.fromEntries(
            Object.entries(filters.value).filter(([_, v]) => v !== null && v !== '' && v !== undefined)
          )
        };

        // Convert array of tag names to comma-separated string
        if (Array.isArray(params.tag)) {
          params.tag = params.tag.join(',');
        }

        const response = await axios.get('/api/leads', { params });
        leads.value = response.data.data;
        pagination.value = {
          total: response.data.total,
          per_page: parseInt(response.data.per_page),
          current_page: response.data.current_page,
          last_page: response.data.last_page,
        };
      } catch (error) {
        console.error('Error fetching leads:', error);
        toast.error('Failed to load leads. Please try again.');
      } finally {
        loading.value = false;
      }
    };

    const fetchLookupData = async () => {
      try {
        const [statusesRes, sourcesRes, usersRes, tagsRes] = await Promise.all([
          axios.get('/api/lookup/statuses'),
          axios.get('/api/lookup/sources'),
          axios.get('/api/lookup/users'),
          axios.get('/api/lookup/tags')
        ]);

        statuses.value = statusesRes.data.data;
        sources.value = sourcesRes.data.data;
        users.value = usersRes.data.data;
        tags.value = tagsRes.data.data;
      } catch (error) {
        console.error('Error fetching lookup data:', error);
        toast.error('Failed to load lookup data. Some features may be limited.');
      }
    };

    const resetFilters = () => {
      filters.value = {
        status_id: null,
        source_id: null,
        assigned_to: null,
        tag: null,
        search: '',
        start_date: '',
        end_date: ''
      };
      currentPage.value = 1;
      fetchLeads();
    };

    const sortBy = (field) => {
      if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
      } else {
        sortField.value = field;
        sortDirection.value = 'asc';
      }
      fetchLeads();
    };

    const getSortIcon = (field) => {
      if (sortField.value !== field) return 'fa-sort';
      return sortDirection.value === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    };

    const changePage = (page) => {
      if (page < 1 || page > pagination.value.last_page) return;
      currentPage.value = page;
      fetchLeads();
    };

    const changePerPage = () => {
      currentPage.value = 1;
      fetchLeads();
    };

    const toggleSelectAll = () => {
      if (selectAll.value) {
        selectedLeads.value = leads.value.map(lead => lead.id);
      } else {
        selectedLeads.value = [];
      }
    };

    const toggleSelectLead = (leadId, event) => {
      event.stopPropagation();
      const index = selectedLeads.value.indexOf(leadId);
      if (index === -1) {
        selectedLeads.value.push(leadId);
      } else {
        selectedLeads.value.splice(index, 1);
      }
      selectAll.value = selectedLeads.value.length === leads.value.length;
    };

    const selectLead = (leadId, event) => {
      // Only select if clicking on the row, not on checkboxes or buttons
      if (event.target.tagName !== 'INPUT' && event.target.tagName !== 'BUTTON' && !event.target.closest('button')) {
        const index = selectedLeads.value.indexOf(leadId);
        if (index === -1) {
          selectedLeads.value = [leadId];
        } else {
          selectedLeads.value = [];
        }
      }
    };

    const viewLead = (leadId) => {
      currentLead.value = leads.value.find(lead => lead.id === leadId);
      showViewModal.value = true;
    };

    const editLead = (leadId) => {
      currentLead.value = leads.value.find(lead => lead.id === leadId);
      showEditModal.value = true;
    };

    const handleLeadSaved = () => {
      closeModal();
      fetchLeads();
      toast.success('Lead saved successfully!');
    };

    const handleLeadDeleted = () => {
      closeModal();
      fetchLeads();
      toast.success('Lead deleted successfully!');
    };

    const confirmDelete = (leadId) => {
      leadToDelete.value = leadId;
      showDeleteModal.value = true;
    };

    const deleteLead = async () => {
      try {
        const leadIds = leadToDelete.value ? [leadToDelete.value] : selectedLeads.value;
        
        await axios.delete('/api/leads/bulk/delete', {
          data: { ids: leadIds }
        });

        toast.success(`Successfully deleted ${leadIds.length} lead(s)`);
        showDeleteModal.value = false;
        leadToDelete.value = null;
        selectedLeads.value = [];
        fetchLeads();
      } catch (error) {
        console.error('Error deleting lead(s):', error);
        toast.error('Failed to delete lead(s). Please try again.');
      }
    };

    const bulkChangeStatus = () => {
      bulkStatusId.value = null;
      showBulkStatusModal.value = true;
    };

    const confirmBulkStatusChange = async () => {
      try {
        await axios.post('/api/leads/bulk/status', {
          ids: selectedLeads.value,
          status_id: bulkStatusId.value
        });

        toast.success(`Status updated for ${selectedLeads.value.length} lead(s)`);
        showBulkStatusModal.value = false;
        selectedLeads.value = [];
        fetchLeads();
      } catch (error) {
        console.error('Error updating status:', error);
        toast.error('Failed to update status. Please try again.');
      }
    };

    const bulkAssign = () => {
      bulkAssignTo.value = null;
      showBulkAssignModal.value = true;
    };

    const confirmBulkAssign = async () => {
      try {
        await axios.post('/api/leads/bulk/assign', {
          ids: selectedLeads.value,
          assigned_to: bulkAssignTo.value
        });

        toast.success(`Assigned ${selectedLeads.value.length} lead(s) to selected user`);
        showBulkAssignModal.value = false;
        selectedLeads.value = [];
        fetchLeads();
      } catch (error) {
        console.error('Error assigning leads:', error);
        toast.error('Failed to assign leads. Please try again.');
      }
    };

    const bulkAddTag = () => {
      bulkTagName.value = '';
      showBulkTagModal.value = true;
    };

    const confirmBulkAddTag = async () => {
      try {
        await axios.post('/api/leads/bulk/tag', {
          ids: selectedLeads.value,
          tag: bulkTagName.value
        });

        toast.success(`Added tag to ${selectedLeads.value.length} lead(s)`);
        showBulkTagModal.value = false;
        selectedLeads.value = [];
        fetchLeads();
      } catch (error) {
        console.error('Error adding tag:', error);
        toast.error('Failed to add tag. Please try again.');
      }
    };

    const confirmBulkDelete = () => {
      leadToDelete.value = null;
      showDeleteModal.value = true;
    };

    const closeModal = () => {
      showCreateModal.value = false;
      showEditModal.value = false;
      showViewModal.value = false;
      showDeleteModal.value = false;
      showBulkStatusModal.value = false;
      showBulkAssignModal.value = false;
      showBulkTagModal.value = false;
      currentLead.value = null;
    };

    const exportLeads = async () => {
      try {
        const params = {
          ...Object.fromEntries(
            Object.entries(filters.value).filter(([_, v]) => v !== null && v !== '' && v !== undefined)
          ),
          export: 'csv'
        };

        // Convert array of tag names to comma-separated string
        if (Array.isArray(params.tag)) {
          params.tag = params.tag.join(',');
        }

        const response = await axios.get('/api/leads/export', {
          params,
          responseType: 'blob'
        });

        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `leads-${new Date().toISOString().split('T')[0]}.csv`);
        document.body.appendChild(link);
        link.click();
        link.remove();
      } catch (error) {
        console.error('Error exporting leads:', error);
        toast.error('Failed to export leads. Please try again.');
      }
    };

    // Helper methods
    const getAvatar = (lead) => {
      if (lead.avatar) return lead.avatar;
      
      // Generate initials avatar
      const name = `${lead.first_name} ${lead.last_name}`.trim();
      const initials = name.split(' ')
        .map(part => part[0])
        .join('')
        .toUpperCase();
      
      const colors = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEEAD',
        '#D4A5A5', '#9B97B2', '#E8FCC2', '#B1CC74', '#E5D4ED'
      ];
      const colorIndex = name.split('').reduce((sum, char) => sum + char.charCodeAt(0), 0) % colors.length;
      
      return `data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'><rect width='100%' height='100%' fill='${colors[colorIndex]}'/><text x='50%' y='50%' font-size='40' text-anchor='middle' dy='.3em' fill='white'>${initials}</text></svg>`;
    };

    const formatDate = (dateString) => {
      if (!dateString) return '';
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateString).toLocaleDateString(undefined, options);
    };

    const formatPhone = (phone) => {
      if (!phone) return '';
      // Simple formatting, can be enhanced with libphonenumber-js for better formatting
      return phone.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    };

    const getContrastColor = (hexColor) => {
      // If the color is light, return dark color and vice versa
      const r = parseInt(hexColor.substr(1, 2), 16);
      const g = parseInt(hexColor.substr(3, 2), 16);
      const b = parseInt(hexColor.substr(5, 2), 16);
      const brightness = (r * 299 + g * 587 + b * 114) / 1000;
      return brightness > 128 ? '#000000' : '#ffffff';
    };

    // Watch for changes in selectedLeads to update selectAll state
    watch(selectedLeads, (newVal) => {
      selectAll.value = newVal.length === leads.value.length && leads.value.length > 0;
    });

    // Watch for filter changes and debounce the API call
    let debounceTimer;
    watch([() => filters.value, currentPage, perPage], () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        fetchLeads();
      }, 300);
    }, { deep: true });

    // Fetch data on component mount
    onMounted(async () => {
      await Promise.all([
        fetchLeads(),
        fetchLookupData()
      ]);
    });

    return {
      // Refs
      loading,
      leads,
      statuses,
      sources,
      users,
      tags,
      selectedLeads,
      selectAll,
      currentLead,
      showCreateModal,
      showEditModal,
      showViewModal,
      showDeleteModal,
      showBulkStatusModal,
      showBulkAssignModal,
      showBulkTagModal,
      bulkStatusId,
      bulkAssignTo,
      bulkTagName,
      leadToDelete,
      sortField,
      sortDirection,
      perPage,
      currentPage,
      pagination,
      filters,
      visiblePages,
      
      // Methods
      fetchLeads,
      resetFilters,
      sortBy,
      getSortIcon,
      changePage,
      changePerPage,
      toggleSelectAll,
      toggleSelectLead,
      selectLead,
      viewLead,
      editLead,
      handleLeadSaved,
      handleLeadDeleted,
      confirmDelete,
      deleteLead,
      bulkChangeStatus,
      confirmBulkStatusChange,
      bulkAssign,
      confirmBulkAssign,
      bulkAddTag,
      confirmBulkAddTag,
      confirmBulkDelete,
      closeModal,
      exportLeads,
      getAvatar,
      formatDate,
      formatPhone,
      getContrastColor
    };
  }
};
</script>

<style scoped>
.leads-container {
  position: relative;
  padding-bottom: 80px;
}

.table th {
  cursor: pointer;
  user-select: none;
  white-space: nowrap;
}

.table th:hover {
  background-color: #f8f9fa;
}

.table td, .table th {
  vertical-align: middle;
}

.avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  overflow: hidden;
  background-color: #f8f9fa;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.bulk-actions-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 1040;
  border-top: 1px solid #dee2e6;
  transition: all 0.3s ease;
}

.modal-backdrop {
  z-index: 1050;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal {
  z-index: 1060;
}

/* Custom scrollbar for table */
.table-responsive::-webkit-scrollbar {
  height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Hover effects */
.table-hover tbody tr:hover {
  background-color: rgba(0, 0, 0, 0.02);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .table-responsive {
    border: 0;
  }
  
  .table thead {
    display: none;
  }
  
  .table, .table tbody, .table tr, .table td {
    display: block;
    width: 100%;
  }
  
  .table tr {
    margin-bottom: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
  }
  
  .table td {
    text-align: right;
    padding-left: 50%;
    position: relative;
    border-bottom: 1px solid #dee2e6;
  }
  
  .table td::before {
    content: attr(data-label);
    position: absolute;
    left: 1rem;
    width: 45%;
    padding-right: 1rem;
    text-align: left;
    font-weight: bold;
  }
  
  .table td:last-child {
    border-bottom: 0;
  }
  
  .bulk-actions-bar {
    padding: 0.5rem;
  }
  
  .bulk-actions-bar .btn-group {
    flex-wrap: wrap;
    gap: 0.25rem;
  }
  
  .bulk-actions-bar .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
  }
}
</style>
