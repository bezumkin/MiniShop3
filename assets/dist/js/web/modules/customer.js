ms3.customer = {
  init () {
    const forms = document.querySelectorAll('.ms3_customer_form')
    forms.forEach(form => ms3.customer.formListener(form))
  },

  formListener (form) {
    const formInputs = form.querySelectorAll('input, textarea')
    formInputs.forEach(input => ms3.customer.changeInputListener(input))
  },

  changeInputListener (input) {
    input.addEventListener('change', async () => {
      const form = input.closest('.ms3_customer_form')
      form.classList.remove('was-validated')
      input.classList.remove('is-invalid')
      input.closest('div').querySelector('.invalid-feedback').textContent = ''

      const formData = new FormData()
      formData.append('key', input.name)
      formData.append('value', input.value)

      const { success, data, message } = await ms3.customer.add(formData)

      if (success === true) {
        input.value = data[input.name]
      } else {
        form.classList.add('was-validated')
        input.classList.add('is-invalid')
        input.closest('div').querySelector('.invalid-feedback').textContent = message
      }
    })
  },

  async add (formData) {
    formData.append('ms3_action', 'customer/add')
    return await ms3.request.send(formData)
  }
}
