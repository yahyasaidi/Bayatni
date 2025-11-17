document.addEventListener("DOMContentLoaded", () => {
  // Select all functionality
  const selectAllCheckbox = document.getElementById("select-all")
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener("change", () => {
      const checkboxes = document.querySelectorAll(".hotel-checkbox")
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

      const selectedHotels = Array.from(document.querySelectorAll(".hotel-checkbox:checked")).map((checkbox) =>
        checkbox.getAttribute("data-id"),
      )

      if (selectedHotels.length === 0) {
        alert("Please select at least one hotel")
        return
      }

      // Confirm before proceeding
      if (selectedAction === "delete") {
        if (!confirm(`Are you sure you want to delete ${selectedHotels.length} hotels?`)) {
          return
        }
      }

      // Process the action
      processHotelBulkAction(selectedAction, selectedHotels)
    })
  }

  function processHotelBulkAction(action, hotelIds) {
    // Here you would typically send an AJAX request to a server endpoint
    console.log(`Processing ${action} for hotels:`, hotelIds)

    // Example implementation
    const formData = new FormData()
    formData.append("action", action)
    formData.append("hotel_ids", JSON.stringify(hotelIds))

    fetch("process_hotel_bulk_action.php", {
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

  // Add CSS for hotel checkboxes
  const style = document.createElement("style")
  style.textContent = `
        .hotel-select {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
        }
        .hotel-select input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .hotel-item {
            position: relative;
        }
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
