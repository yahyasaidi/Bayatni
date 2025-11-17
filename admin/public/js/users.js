document.addEventListener("DOMContentLoaded", () => {
  // Select all functionality
  const selectAllCheckbox = document.getElementById("select-all")
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", () => {
      const checkboxes = document.querySelectorAll(".user-select")
      checkboxes.forEach((checkbox) => {
        checkbox.checked = selectAllCheckbox.checked
      })
    })
  }

  // Bulk actions
  const bulkActionSelect = document.getElementById("bulk-action")
  const applyBtn = document.querySelector(".apply-btn")

  if (applyBtn) {
    applyBtn.addEventListener("click", () => {
      const selectedAction = bulkActionSelect.value
      if (!selectedAction) {
        alert("Please select an action")
        return
      }

      const selectedUsers = Array.from(document.querySelectorAll(".user-select:checked")).map((checkbox) =>
        checkbox.closest("tr").querySelector("td:nth-child(2)").textContent.replace("#User-", ""),
      )

      if (selectedUsers.length === 0) {
        alert("Please select at least one user")
        return
      }

      // Confirm before proceeding
      if (selectedAction === "delete") {
        if (!confirm(`Are you sure you want to delete ${selectedUsers.length} users?`)) {
          return
        }
      }

      // Process the action
      processUserBulkAction(selectedAction, selectedUsers)
    })
  }

  function processUserBulkAction(action, userIds) {
    // Here you would typically send an AJAX request to a server endpoint
    console.log(`Processing ${action} for users:`, userIds)

    // Example implementation
    const formData = new FormData()
    formData.append("action", action)
    formData.append("user_ids", JSON.stringify(userIds))

    fetch("process_user_bulk_action.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert(data.message)
          // Reload the page to reflect changes
          window.location.reload()
        } else {
          alert("Error: " + data.message)
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        alert("An error occurred while processing your request")
      })
  }

  // Add CSS for bulk actions
  const style = document.createElement("style")
  style.textContent = `
        .bulk-actions {
            display: flex;
            margin-top: 20px;
            gap: 10px;
        }
        .bulk-actions select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .apply-btn {
            padding: 8px 16px;
            background-color: #4a6cf7;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .apply-btn:hover {
            background-color: #3a5bd9;
        }
    `
  document.head.appendChild(style)
})
