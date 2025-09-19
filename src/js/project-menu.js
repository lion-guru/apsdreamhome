// Project Menu Module

class ProjectMenu {
    constructor() {
        this.menuContainer = document.querySelector('#project-menu');
        this.projects = [];
        this.init();
    }

    async init() {
        await this.fetchProjects();
        this.renderMenu();
        this.attachEventListeners();
    }

    async fetchProjects() {
        try {
            const response = await fetch('/admin/api/projects');
            const data = await response.json();
            if (data.success) {
                this.projects = data.data;
            }
        } catch (error) {
            console.error('Failed to fetch projects:', error);
        }
    }

    renderMenu() {
        if (!this.menuContainer) return;

        // Group projects by status
        const groupedProjects = this.projects.reduce((acc, project) => {
            if (!acc[project.status]) {
                acc[project.status] = [];
            }
            acc[project.status].push(project);
            return acc;
        }, {});

        // Create menu HTML
        let menuHTML = '<ul class="project-menu-list">';

        // Featured projects first
        const featuredProjects = this.projects.filter(p => p.featured);
        if (featuredProjects.length > 0) {
            menuHTML += '<li class="menu-category"><span>Featured Projects</span><ul>';
            featuredProjects.forEach(project => {
                menuHTML += this.createProjectMenuItem(project);
            });
            menuHTML += '</ul></li>';
        }

        // Then by status
        const statusOrder = ['ongoing', 'upcoming', 'completed'];
        statusOrder.forEach(status => {
            if (groupedProjects[status] && groupedProjects[status].length > 0) {
                menuHTML += `
                    <li class="menu-category">
                        <span>${status.charAt(0).toUpperCase() + status.slice(1)} Projects</span>
                        <ul>
                            ${groupedProjects[status].map(project => this.createProjectMenuItem(project)).join('')}
                        </ul>
                    </li>
                `;
            }
        });

        menuHTML += '</ul>';
        this.menuContainer.innerHTML = menuHTML;
    }

    createProjectMenuItem(project) {
        return `
            <li class="project-item">
                <a href="/project.php?slug=${project.slug}" class="project-link">
                    <span class="project-name">${project.name}</span>
                    <span class="project-location">${project.location}</span>
                </a>
            </li>
        `;
    }

    attachEventListeners() {
        // Add hover effects or other interactions if needed
        const menuItems = this.menuContainer.querySelectorAll('.project-item');
        menuItems.forEach(item => {
            item.addEventListener('mouseenter', () => {
                // Add hover effects
            });
        });

        // Add mobile menu toggle if needed
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                this.menuContainer.classList.toggle('active');
            });
        }
    }
}

// Initialize the project menu when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ProjectMenu();
});