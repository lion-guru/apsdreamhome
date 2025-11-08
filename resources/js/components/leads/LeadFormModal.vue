<template>
  <div 
    class="modal fade" 
    :class="{ 'show d-block': show }" 
    tabindex="-1" 
    role="dialog"
    @click.self="close"
  >
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ isEditMode ? 'Edit Lead' : 'Create New Lead' }}</h5>
          <button type="button" class="btn-close" @click="close" aria-label="Close"></button>
        </div>
        <form @submit.prevent="submitForm">
          <div class="modal-body">
            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <div v-else>
              <ul class="nav nav-tabs mb-4" id="leadFormTabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <button 
                    class="nav-link active" 
                    id="details-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#details" 
                    type="button" 
                    role="tab" 
                    aria-controls="details" 
                    aria-selected="true"
                  >
                    <i class="fas fa-user me-1"></i> Details
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button 
                    class="nav-link" 
                    id="contact-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#contact" 
                    type="button" 
                    role="tab" 
                    aria-controls="contact" 
                    aria-selected="false"
                  >
                    <i class="fas fa-address-card me-1"></i> Contact
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button 
                    class="nav-link" 
                    id="other-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#other" 
                    type="button" 
                    role="tab" 
                    aria-controls="other" 
                    aria-selected="false"
                  >
                    <i class="fas fa-info-circle me-1"></i> Other Info
                  </button>
                </li>
              </ul>

              <div class="tab-content" id="leadFormTabContent">
                <!-- Details Tab -->
                <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="first_name" 
                        v-model="formData.first_name" 
                        :class="{ 'is-invalid': errors.first_name }"
                        required
                      >
                      <div class="invalid-feedback" v-if="errors.first_name">
                        {{ errors.first_name[0] }}
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="last_name" 
                        v-model="formData.last_name" 
                        :class="{ 'is-invalid': errors.last_name }"
                        required
                      >
                      <div class="invalid-feedback" v-if="errors.last_name">
                        {{ errors.last_name[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="company" class="form-label">Company</label>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="company" 
                        v-model="formData.company"
                        :class="{ 'is-invalid': errors.company }"
                      >
                      <div class="invalid-feedback" v-if="errors.company">
                        {{ errors.company[0] }}
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="job_title" class="form-label">Job Title</label>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="job_title" 
                        v-model="formData.job_title"
                        :class="{ 'is-invalid': errors.job_title }"
                      >
                      <div class="invalid-feedback" v-if="errors.job_title">
                        {{ errors.job_title[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="status_id" class="form-label">Status <span class="text-danger">*</span></label>
                      <v-select 
                        v-model="formData.status_id" 
                        :options="statuses" 
                        label="name" 
                        :reduce="status => status.id"
                        placeholder="Select status"
                        :class="{ 'is-invalid': errors.status_id }"
                        required
                      ></v-select>
                      <div class="invalid-feedback" v-if="errors.status_id">
                        {{ errors.status_id[0] }}
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="source_id" class="form-label">Source</label>
                      <v-select 
                        v-model="formData.source_id" 
                        :options="sources" 
                        label="name" 
                        :reduce="source => source.id"
                        placeholder="Select source"
                        :class="{ 'is-invalid': errors.source_id }"
                      ></v-select>
                      <div class="invalid-feedback" v-if="errors.source_id">
                        {{ errors.source_id[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="assigned_to" class="form-label">Assigned To</label>
                    <v-select 
                      v-model="formData.assigned_to" 
                      :options="users" 
                      label="name" 
                      :reduce="user => user.id"
                      placeholder="Select user"
                      :class="{ 'is-invalid': errors.assigned_to }"
                    ></v-select>
                    <div class="invalid-feedback" v-if="errors.assigned_to">
                      {{ errors.assigned_to[0] }}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="tags" class="form-label">Tags</label>
                    <v-select 
                      v-model="formData.tags" 
                      :options="availableTags" 
                      label="name" 
                      multiple 
                      taggable
                      push-tags
                      :reduce="tag => tag.name"
                      placeholder="Add tags..."
                      :class="{ 'is-invalid': errors.tags }"
                    ></v-select>
                    <div class="invalid-feedback" v-if="errors.tags">
                      {{ errors.tags[0] }}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea 
                      class="form-control" 
                      id="description" 
                      rows="3" 
                      v-model="formData.description"
                      :class="{ 'is-invalid': errors.description }"
                    ></textarea>
                    <div class="invalid-feedback" v-if="errors.description">
                      {{ errors.description[0] }}
                    </div>
                  </div>
                </div>
                
                <!-- Contact Tab -->
                <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                      <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        v-model="formData.email"
                        :class="{ 'is-invalid': errors.email }"
                        required
                      >
                      <div class="invalid-feedback" v-if="errors.email">
                        {{ errors.email[0] }}
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="phone" class="form-label">Phone</label>
                      <input 
                        type="tel" 
                        class="form-control" 
                        id="phone" 
                        v-model="formData.phone"
                        :class="{ 'is-invalid': errors.phone }"
                        v-mask="'(###) ###-####'"
                      >
                      <div class="invalid-feedback" v-if="errors.phone">
                        {{ errors.phone[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label for="mobile" class="form-label">Mobile</label>
                      <input 
                        type="tel" 
                        class="form-control" 
                        id="mobile" 
                        v-model="formData.mobile"
                        :class="{ 'is-invalid': errors.mobile }"
                        v-mask="'(###) ###-####'"
                      >
                      <div class="invalid-feedback" v-if="errors.mobile">
                        {{ errors.mobile[0] }}
                      </div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label for="website" class="form-label">Website</label>
                      <div class="input-group">
                        <span class="input-group-text">https://</span>
                        <input 
                          type="text" 
                          class="form-control" 
                          id="website" 
                          v-model="formData.website"
                          :class="{ 'is-invalid': errors.website }"
                          placeholder="example.com"
                        >
                        <div class="invalid-feedback" v-if="errors.website">
                          {{ errors.website[0] }}
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input 
                      type="text" 
                      class="form-control mb-2" 
                      id="address" 
                      v-model="formData.address"
                      :class="{ 'is-invalid': errors.address }"
                      placeholder="Street address"
                    >
                    <div class="row">
                      <div class="col-md-6 mb-2">
                        <input 
                          type="text" 
                          class="form-control" 
                          v-model="formData.city"
                          :class="{ 'is-invalid': errors.city }"
                          placeholder="City"
                        >
                        <div class="invalid-feedback" v-if="errors.city">
                          {{ errors.city[0] }}
                        </div>
                      </div>
                      <div class="col-md-3 mb-2">
                        <input 
                          type="text" 
                          class="form-control" 
                          v-model="formData.state"
                          :class="{ 'is-invalid': errors.state }"
                          placeholder="State"
                        >
                        <div class="invalid-feedback" v-if="errors.state">
                          {{ errors.state[0] }}
                        </div>
                      </div>
                      <div class="col-md-3 mb-2">
                        <input 
                          type="text" 
                          class="form-control" 
                          v-model="formData.postal_code"
                          :class="{ 'is-invalid': errors.postal_code }"
                          placeholder="ZIP/Postal Code"
                        >
                        <div class="invalid-feedback" v-if="errors.postal_code">
                          {{ errors.postal_code[0] }}
                        </div>
                      </div>
                    </div>
                    <div class="mb-2">
                      <select 
                        class="form-select" 
                        v-model="formData.country"
                        :class="{ 'is-invalid': errors.country }"
                      >
                        <option value="">Select Country</option>
                        <option v-for="country in countries" :key="country.code" :value="country.name">
                          {{ country.name }}
                        </option>
                      </select>
                      <div class="invalid-feedback" v-if="errors.country">
                        {{ errors.country[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Preferred Contact Method</label>
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="radio" 
                        id="contact_email" 
                        value="email" 
                        v-model="formData.preferred_contact_method"
                      >
                      <label class="form-check-label" for="contact_email">
                        Email
                      </label>
                    </div>
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="radio" 
                        id="contact_phone" 
                        value="phone" 
                        v-model="formData.preferred_contact_method"
                      >
                      <label class="form-check-label" for="contact_phone">
                        Phone
                      </label>
                    </div>
                    <div class="form-check">
                      <input 
                        class="form-check-input" 
                        type="radio" 
                        id="contact_sms" 
                        value="sms" 
                        v-model="formData.preferred_contact_method"
                      >
                      <label class="form-check-label" for="contact_sms">
                        SMS
                      </label>
                    </div>
                  </div>
                </div>
                
                <!-- Other Info Tab -->
                <div class="tab-pane fade" id="other" role="tabpanel" aria-labelledby="other-tab">
                  <div class="mb-3">
                    <label for="lead_score" class="form-label">Lead Score</label>
                    <input 
                      type="number" 
                      class="form-control w-auto" 
                      id="lead_score" 
                      v-model.number="formData.lead_score"
                      min="0" 
                      max="100"
                      :class="{ 'is-invalid': errors.lead_score }"
                    >
                    <div class="form-text">Score from 0 to 100 indicating lead quality</div>
                    <div class="invalid-feedback" v-if="errors.lead_score">
                      {{ errors.lead_score[0] }}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="facebook" class="form-label">Facebook</label>
                    <div class="input-group">
                      <span class="input-group-text">facebook.com/</span>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="facebook" 
                        v-model="formData.facebook"
                        :class="{ 'is-invalid': errors.facebook }"
                      >
                      <div class="invalid-feedback" v-if="errors.facebook">
                        {{ errors.facebook[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="twitter" class="form-label">Twitter</label>
                    <div class="input-group">
                      <span class="input-group-text">twitter.com/</span>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="twitter" 
                        v-model="formData.twitter"
                        :class="{ 'is-invalid': errors.twitter }"
                      >
                      <div class="invalid-feedback" v-if="errors.twitter">
                        {{ errors.twitter[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="linkedin" class="form-label">LinkedIn</label>
                    <div class="input-group">
                      <span class="input-group-text">linkedin.com/in/</span>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="linkedin" 
                        v-model="formData.linkedin"
                        :class="{ 'is-invalid': errors.linkedin }"
                      >
                      <div class="invalid-feedback" v-if="errors.linkedin">
                        {{ errors.linkedin[0] }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="skype" class="form-label">Skype</label>
                    <input 
                      type="text" 
                      class="form-control" 
                      id="skype" 
                      v-model="formData.skype"
                      :class="{ 'is-invalid': errors.skype }"
                    >
                    <div class="invalid-feedback" v-if="errors.skype">
                      {{ errors.skype[0] }}
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="custom_fields" class="form-label">Custom Fields</label>
                    <div v-for="field in customFields" :key="field.id" class="mb-2">
                      <label :for="`custom_field_${field.id}" class="form-label small mb-1">{{ field.field_label }}</label>
                      <input 
                        v-if="field.field_type === 'text' || field.field_type === 'number' || field.field_type === 'date'"
                        :type="field.field_type" 
                        class="form-control form-control-sm" 
                        :id="`custom_field_${field.id}`"
                        v-model="formData.custom_fields[field.id]"
                      >
                      <textarea 
                        v-else-if="field.field_type === 'textarea'"
                        class="form-control form-control-sm" 
                        :id="`custom_field_${field.id}`"
                        v-model="formData.custom_fields[field.id]"
                        rows="2"
                      ></textarea>
                      <select 
                        v-else-if="field.field_type === 'select'"
                        class="form-select form-select-sm" 
                        :id="`custom_field_${field.id}`"
                        v-model="formData.custom_fields[field.id]"
                      >
                        <option value="">Select {{ field.field_label }}</option>
                        <option v-for="option in field.field_options" :key="option.value" :value="option.value">
                          {{ option.label }}
                        </option>
                      </select>
                      <div v-else-if="field.field_type === 'checkbox'" class="form-check">
                        <input 
                          type="checkbox" 
                          class="form-check-input" 
                          :id="`custom_field_${field.id}`"
                          v-model="formData.custom_fields[field.id]"
                          :true-value="field.field_options?.true_value || 'Yes'"
                          :false-value="field.field_options?.false_value || 'No'"
                        >
                        <label class="form-check-label" :for="`custom_field_${field.id}`">
                          {{ field.field_options?.checkbox_label || field.field_label }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
            <button type="button" class="btn btn-outline-secondary me-auto" @click="saveAndNew" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              Save & New
            </button>
            <button type="submit" class="btn btn-primary" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
              {{ isEditMode ? 'Update' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <!-- Overlay -->
  <div v-if="show" class="modal-backdrop fade show" @click="close"></div>
</template>

<script>
import axios from 'axios';
import { ref, computed, watch, onMounted } from 'vue';
import { useToast } from 'vue-toast-notification';
import 'vue-toast-notification/dist/theme-sugar.css';

export default {
  name: 'LeadFormModal',
  props: {
    show: {
      type: Boolean,
      default: false
    },
    lead: {
      type: Object,
      default: null
    },
    statuses: {
      type: Array,
      required: true
    },
    sources: {
      type: Array,
      required: true
    },
    users: {
      type: Array,
      required: true
    },
    tags: {
      type: Array,
      required: true
    }
  },
  emits: ['saved', 'closed'],
  setup(props, { emit }) {
    const toast = useToast();
    const loading = ref(false);
    const submitting = ref(false);
    const errors = ref({});
    const customFields = ref([]);
    
    // Form data with default values
    const formData = ref({
      first_name: '',
      last_name: '',
      company: '',
      job_title: '',
      email: '',
      phone: '',
      mobile: '',
      website: '',
      address: '',
      city: '',
      state: '',
      postal_code: '',
      country: '',
      status_id: null,
      source_id: null,
      assigned_to: null,
      description: '',
      tags: [],
      lead_score: 0,
      preferred_contact_method: 'email',
      facebook: '',
      twitter: '',
      linkedin: '',
      skype: '',
      custom_fields: {}
    });
    
    // Countries list for dropdown
    const countries = [
      { code: 'US', name: 'United States' },
      { code: 'CA', name: 'Canada' },
      { code: 'GB', name: 'United Kingdom' },
      { code: 'AU', name: 'Australia' },
      // Add more countries as needed
    ];
    
    // Computed properties
    const isEditMode = computed(() => !!props.lead?.id);
    
    // Filter out tags that are already selected
    const availableTags = computed(() => {
      return props.tags.filter(tag => !formData.value.tags.includes(tag.name));
    });
    
    // Watch for lead prop changes (when editing)
    watch(() => props.lead, (newVal) => {
      if (newVal) {
        loadLeadData(newVal.id);
      } else {
        resetForm();
      }
    }, { immediate: true });
    
    // Watch for show prop changes to handle modal open/close
    watch(() => props.show, (newVal) => {
      if (newVal) {
        // Reset form when opening modal for a new lead
        if (!props.lead) {
          resetForm();
        }
        // Load custom fields when modal is shown
        loadCustomFields();
      }
    });
    
    // Methods
    const loadLeadData = async (leadId) => {
      if (!leadId) return;
      
      try {
        loading.value = true;
        const response = await axios.get(`/api/leads/${leadId}`);
        const leadData = response.data.data;
        
        // Map the API response to form data
        formData.value = {
          ...formData.value,
          ...leadData,
          // Map nested relationships
          status_id: leadData.status?.id || null,
          source_id: leadData.source?.id || null,
          assigned_to: leadData.assigned_to_user?.id || null,
          tags: leadData.tags?.map(tag => tag.name) || [],
          // Map custom fields
          custom_fields: { ...leadData.custom_fields || {} }
        };
        
        // Load custom fields after form data is set
        await loadCustomFields();
      } catch (error) {
        console.error('Error loading lead data:', error);
        toast.error('Failed to load lead data. Please try again.');
        close();
      } finally {
        loading.value = false;
      }
    };
    
    const loadCustomFields = async () => {
      try {
        const response = await axios.get('/api/lookup/custom-fields');
        customFields.value = response.data.data || [];
        
        // Initialize custom fields in form data if they don't exist
        customFields.value.forEach(field => {
          if (!(field.id in formData.value.custom_fields)) {
            formData.value.custom_fields[field.id] = field.default_value || '';
          }
        });
      } catch (error) {
        console.error('Error loading custom fields:', error);
      }
    };
    
    const resetForm = () => {
      formData.value = {
        first_name: '',
        last_name: '',
        company: '',
        job_title: '',
        email: '',
        phone: '',
        mobile: '',
        website: '',
        address: '',
        city: '',
        state: '',
        postal_code: '',
        country: '',
        status_id: props.statuses.length > 0 ? props.statuses[0].id : null,
        source_id: null,
        assigned_to: null,
        description: '',
        tags: [],
        lead_score: 0,
        preferred_contact_method: 'email',
        facebook: '',
        twitter: '',
        linkedin: '',
        skype: '',
        custom_fields: {}
      };
      errors.value = {};
    };
    
    const submitForm = async (saveAndNew = false) => {
      try {
        submitting.value = true;
        errors.value = {};
        
        // Prepare the data for submission
        const payload = { ...formData.value };
        
        // If it's an update, use PUT, otherwise POST
        const url = isEditMode.value 
          ? `/api/leads/${props.lead.id}` 
          : '/api/leads';
          
        const method = isEditMode.value ? 'put' : 'post';
        
        const response = await axios[method](url, payload);
        
        toast.success(`Lead ${isEditMode.value ? 'updated' : 'created'} successfully!`);
        
        // Emit saved event with the response data
        emit('saved', response.data.data);
        
        // Reset form if save and new, otherwise close the modal
        if (saveAndNew) {
          resetForm();
        } else {
          close();
        }
      } catch (error) {
        if (error.response && error.response.status === 422) {
          // Validation errors
          errors.value = error.response.data.errors || {};
          toast.error('Please correct the errors in the form.');
        } else {
          console.error('Error saving lead:', error);
          toast.error(`Failed to ${isEditMode.value ? 'update' : 'create'} lead. Please try again.`);
        }
      } finally {
        submitting.value = false;
      }
    };
    
    const saveAndNew = () => {
      submitForm(true);
    };
    
    const close = () => {
      emit('closed');
    };
    
    return {
      // Refs
      loading,
      submitting,
      errors,
      formData,
      customFields,
      countries,
      
      // Computed
      isEditMode,
      availableTags,
      
      // Methods
      submitForm,
      saveAndNew,
      close
    };
  }
};
</script>

<style scoped>
.modal {
  z-index: 1060;
}

.modal-backdrop {
  z-index: 1050;
  background-color: rgba(0, 0, 0, 0.5);
}

.nav-tabs .nav-link {
  color: #6c757d;
  font-weight: 500;
}

.nav-tabs .nav-link.active {
  font-weight: 600;
  color: #0d6efd;
  border-bottom: 2px solid #0d6efd;
  margin-bottom: -1px;
}

/* Custom styling for v-select */
:deep(.v-select) {
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
}

:deep(.v-select.vs--open) {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

:deep(.vs__dropdown-toggle) {
  border: none;
  padding: 0.25rem 0.5rem;
  min-height: 38px;
}

:deep(.vs__selected-options) {
  padding: 0;
  flex-wrap: wrap;
}

:deep(.vs__search) {
  margin: 0;
  padding: 0;
  border: none;
  font-size: 0.9rem;
}

:deep(.vs__selected) {
  margin: 2px 4px 2px 0;
  padding: 2px 6px;
  background-color: #e9ecef;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  color: #212529;
}

:deep(.vs__deselect) {
  margin-left: 4px;
}

:deep(.vs__actions) {
  padding: 0 6px 0 3px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .modal-dialog {
    margin: 0.5rem;
    max-width: calc(100% - 1rem);
  }
  
  .btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
  }
}
</style>
