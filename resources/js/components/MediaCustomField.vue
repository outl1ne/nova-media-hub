<template>
  <div class="o1-flex o1-flex-col o1-mb-4">
    <label>{{ field.name }}</label>
    
    <!-- Text Field -->
    <input
      v-if="field.component === 'text-field'"
      type="text"
      class="w-full form-control form-input form-input-bordered"
      :value="fieldValue"
      @input="handleInput"
      :placeholder="field.placeholder || ''"
    />
    
    <!-- Textarea Field -->
    <textarea
      v-else-if="field.component === 'textarea-field'"
      class="w-full form-control form-input form-input-bordered"
      :value="fieldValue"
      @input="handleInput"
      :placeholder="field.placeholder || ''"
      rows="3"
    />
    
    <!-- Number Field -->
    <input
      v-else-if="field.component === 'number-field'"
      type="number"
      class="w-full form-control form-input form-input-bordered"
      :value="fieldValue"
      @input="handleInput"
      :placeholder="field.placeholder || ''"
    />
    
    <!-- Select Field -->
    <select
      v-else-if="field.component === 'select-field'"
      class="w-full form-control form-select form-select-bordered"
      :value="fieldValue"
      @change="handleInput"
    >
      <option value="">{{ field.placeholder || 'Select an option' }}</option>
      <option
        v-for="option in field.options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    
    <!-- Boolean Field -->
    <div v-else-if="field.component === 'boolean-field'" class="o1-flex o1-items-center">
      <input
        type="checkbox"
        class="form-checkbox"
        :checked="fieldValue"
        @change="handleBooleanInput"
      />
      <span class="o1-ml-2">{{ field.trueLabel || 'Yes' }}</span>
    </div>
    
    <!-- Date Field -->
    <input
      v-else-if="field.component === 'date-field'"
      type="date"
      class="w-full form-control form-input form-input-bordered"
      :value="fieldValue"
      @input="handleInput"
    />
    
    <!-- DateTime Field -->
    <input
      v-else-if="field.component === 'datetime-field'"
      type="datetime-local"
      class="w-full form-control form-input form-input-bordered"
      :value="fieldValue"
      @input="handleInput"
    />
    
    <!-- Default fallback for other field types -->
    <input
      v-else
      type="text"
      class="w-full form-control form-input form-input-bordered"
      :value="fieldValue"
      @input="handleInput"
      :placeholder="field.placeholder || ''"
    />
    
    <!-- Help text -->
    <p v-if="field.helpText" class="o1-mt-1 o1-text-sm o1-text-gray-600">
      {{ field.helpText }}
    </p>
  </div>
</template>

<script>
export default {
  props: {
    field: {
      type: Object,
      required: true
    },
    modelValue: {
      default: null
    }
  },
  
  emits: ['update:modelValue'],
  
  computed: {
    fieldValue() {
      // Get the current value from the field or modelValue
      return this.field.value !== undefined ? this.field.value : this.modelValue;
    }
  },
  
  methods: {
    handleInput(event) {
      const value = event.target.value;
      this.$emit('update:modelValue', value);
      
      // Update the field value directly for immediate UI feedback
      if (this.field.value !== undefined) {
        this.field.value = value;
      }
    },
    
    handleBooleanInput(event) {
      const value = event.target.checked;
      this.$emit('update:modelValue', value);
      
      // Update the field value directly for immediate UI feedback
      if (this.field.value !== undefined) {
        this.field.value = value;
      }
    }
  }
};
</script>
