# 🌿 Interactive Map — Badan Warisan Malaysia Heritage Garden

An interactive plant map web application developed as part of a **SULAM (Service Learning Malaysia – University for Society)** project, in collaboration with **Badan Warisan Malaysia (BWM)** — Malaysia's leading heritage conservation NGO.

The system allows tourists and visitors to explore the heritage garden at BWM's Heritage Centre (No. 2, Jalan Stonor, Kuala Lumpur) by viewing an interactive map with clickable plant markers, each displaying detailed information about the plant's name, description, and cultural or heritage significance.

**Live Demo:** [https://interactive-map.page.gd/user/](https://interactive-map.page.gd/user/)

---

## Background

Badan Warisan Malaysia's Heritage Centre is surrounded by a heritage garden containing over 50 species of trees and plants of environmental, cultural, and historic significance to Malaysia — including traditional herbs (*ulam*), indigenous flora, and plants tied to Malay heritage. This project digitises that garden experience, giving visitors a self-guided way to explore and learn about each plant on-site.

This application was built under the SULAM framework, where university students apply their technical skills to serve real community and societal needs — in this case, supporting heritage education and eco-tourism at BWM.

---

## Features

### 👤 User Page (Public)
Accessible at: `https://interactive-map.page.gd/user/`

- View the interactive heritage garden map
- Click on colour-coded markers to see plant details
- Each marker displays the plant's name, description, image, and an optional reference link
- Designed for tourists and garden visitors to use on-site or remotely

### 🔐 Admin Panel
*Restricted — admin credentials required*

- Upload and update the garden map image
- Place new plant markers on the map by setting coordinates
- Edit existing plant details (name, description, image, link, marker colour)
- Remove outdated or incorrect markers
- Manage multiple map configurations

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP |
| Database | MariaDB (MySQL) via phpMyAdmin |
| Frontend | HTML, CSS, JavaScript |
| Hosting | InfinityFree |

---

## Database Schema

### `map_config`
Stores the uploaded map image configuration.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary key |
| `map_image` | VARCHAR(255) | Path/URL to the garden map image |
| `created_at` | TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | Last update time |

### `map_points`
Stores individual plant point data rendered on the map.

| Column | Type | Description |
|---|---|---|
| `id` | INT | Primary key |
| `title` | VARCHAR(255) | Plant name / point label |
| `description` | TEXT | Plant details shown when marker is clicked |
| `image` | VARCHAR(255) | Plant image URL |
| `link` | VARCHAR(255) | Optional external reference link |
| `x_coordinate` | DECIMAL(10,6) | Horizontal position on the map |
| `y_coordinate` | DECIMAL(10,6) | Vertical position on the map |
| `icon_color` | VARCHAR(7) | Marker colour (hex, default `#FF0000`) |
| `created_at` | TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | Last update time |

---

## Stakeholder

This project was developed in partnership with:

**[Badan Warisan Malaysia (The Heritage of Malaysia Trust)](https://badanwarisanmalaysia.org/)**
No. 2, Jalan Stonor, 50450 Kuala Lumpur
— Malaysia's leading NGO for the conservation and preservation of built and natural heritage since 1983.

---

## Contributors

- [@NuHazim](https://github.com/NuHazim)
