document.addEventListener('DOMContentLoaded', () => {
  const setFilledState = (field) => {
    const group = field.closest('.input-group-outline');
    if (!group) return;

    const value = field.value;
    if (value !== null && String(value).trim() !== '') {
      group.classList.add('is-filled');
    } else {
      group.classList.remove('is-filled');
    }
  };

  const handleFocus = (event) => {
    const group = event.target.closest('.input-group-outline');
    if (group) {
      group.classList.add('is-focused');
    }
  };

  const handleBlur = (event) => {
    const group = event.target.closest('.input-group-outline');
    if (group) {
      group.classList.remove('is-focused');
      setFilledState(event.target);
    }
  };

  document
    .querySelectorAll('.input-group-outline .form-control')
    .forEach((field) => {
      setFilledState(field);

      field.addEventListener('focus', handleFocus);
      field.addEventListener('blur', handleBlur);
      field.addEventListener('input', () => setFilledState(field));
      field.addEventListener('change', () => setFilledState(field));
    });
});
