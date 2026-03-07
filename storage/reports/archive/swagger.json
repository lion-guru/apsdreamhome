{
    "openapi": "3.0.0",
    "info": {
        "title": "APS Dream Home API",
        "description": "Complete Real Estate Management System API",
        "version": "2.0.0",
        "contact": {
            "name": "APS Dream Home Support",
            "email": "support@apsdreamhome.com"
        },
        "license": {
            "name": "MIT",
            "url": "https:\/\/opensource.org\/licenses\/MIT"
        }
    },
    "servers": [
        {
            "url": "https:\/\/api.apsdreamhome.com",
            "description": "Production server"
        },
        {
            "url": "http:\/\/localhost\/apsdreamhome",
            "description": "Development server"
        }
    ],
    "paths": {
        "\/api\/health": {
            "get": {
                "summary": "Health check endpoint",
                "description": "Health check endpoint",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/status": {
            "get": {
                "summary": "System status endpoint",
                "description": "System status endpoint",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/auth\/login": {
            "post": {
                "summary": "User login",
                "description": "User login",
                "tags": [
                    "Authentication"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/auth\/logout": {
            "post": {
                "summary": "User logout",
                "description": "User logout",
                "tags": [
                    "Authentication"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/auth\/register": {
            "post": {
                "summary": "User registration",
                "description": "User registration",
                "tags": [
                    "Authentication"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/auth\/refresh": {
            "post": {
                "summary": "Token refresh",
                "description": "Token refresh",
                "tags": [
                    "Authentication"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/properties": {
            "get": {
                "summary": "Get all properties",
                "description": "Get all properties",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create new property",
                "description": "Create new property",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/properties\/{id}": {
            "get": {
                "summary": "Get property by ID",
                "description": "Get property by ID",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "put": {
                "summary": "Update property",
                "description": "Update property",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "delete": {
                "summary": "Delete property",
                "description": "Delete property",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/properties\/search": {
            "get": {
                "summary": "Search properties",
                "description": "Search properties",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/properties\/featured": {
            "get": {
                "summary": "Get featured properties",
                "description": "Get featured properties",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/projects": {
            "get": {
                "summary": "Get all projects",
                "description": "Get all projects",
                "tags": [
                    "Projects"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create new project",
                "description": "Create new project",
                "tags": [
                    "Projects"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/projects\/{id}": {
            "get": {
                "summary": "Get project by ID",
                "description": "Get project by ID",
                "tags": [
                    "Projects"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "put": {
                "summary": "Update project",
                "description": "Update project",
                "tags": [
                    "Projects"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "delete": {
                "summary": "Delete project",
                "description": "Delete project",
                "tags": [
                    "Projects"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/users": {
            "get": {
                "summary": "Get all users",
                "description": "Get all users",
                "tags": [
                    "Users"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/users\/{id}": {
            "get": {
                "summary": "Get user by ID",
                "description": "Get user by ID",
                "tags": [
                    "Users"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "put": {
                "summary": "Update user",
                "description": "Update user",
                "tags": [
                    "Users"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/customers": {
            "get": {
                "summary": "Get all customers",
                "description": "Get all customers",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create customer",
                "description": "Create customer",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/customers\/{id}": {
            "get": {
                "summary": "Get customer by ID",
                "description": "Get customer by ID",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/agents": {
            "get": {
                "summary": "Get all agents",
                "description": "Get all agents",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create agent",
                "description": "Create agent",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/agents\/{id}": {
            "get": {
                "summary": "Get agent by ID",
                "description": "Get agent by ID",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/associates": {
            "get": {
                "summary": "Get all associates",
                "description": "Get all associates",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create associate",
                "description": "Create associate",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/associates\/{id}": {
            "get": {
                "summary": "Get associate by ID",
                "description": "Get associate by ID",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/leads": {
            "get": {
                "summary": "Get all leads",
                "description": "Get all leads",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create lead",
                "description": "Create lead",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/leads\/{id}": {
            "get": {
                "summary": "Get lead by ID",
                "description": "Get lead by ID",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "put": {
                "summary": "Update lead",
                "description": "Update lead",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/crm\/contacts": {
            "get": {
                "summary": "Get CRM contacts",
                "description": "Get CRM contacts",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create CRM contact",
                "description": "Create CRM contact",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/payments": {
            "get": {
                "summary": "Get all payments",
                "description": "Get all payments",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create payment",
                "description": "Create payment",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/payments\/{id}": {
            "get": {
                "summary": "Get payment by ID",
                "description": "Get payment by ID",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/transactions": {
            "get": {
                "summary": "Get all transactions",
                "description": "Get all transactions",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create transaction",
                "description": "Create transaction",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/gallery": {
            "get": {
                "summary": "Get gallery images",
                "description": "Get gallery images",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/gallery\/upload": {
            "post": {
                "summary": "Upload gallery image",
                "description": "Upload gallery image",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/gallery\/{id}": {
            "delete": {
                "summary": "Delete gallery image",
                "description": "Delete gallery image",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/media": {
            "get": {
                "summary": "Get all media files",
                "description": "Get all media files",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/media\/upload": {
            "post": {
                "summary": "Upload media file",
                "description": "Upload media file",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/blog\/posts": {
            "get": {
                "summary": "Get blog posts",
                "description": "Get blog posts",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create blog post",
                "description": "Create blog post",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/blog\/posts\/{id}": {
            "get": {
                "summary": "Get blog post by ID",
                "description": "Get blog post by ID",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "put": {
                "summary": "Update blog post",
                "description": "Update blog post",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "delete": {
                "summary": "Delete blog post",
                "description": "Delete blog post",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/blog\/categories": {
            "get": {
                "summary": "Get blog categories",
                "description": "Get blog categories",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/analytics\/dashboard": {
            "get": {
                "summary": "Get dashboard analytics",
                "description": "Get dashboard analytics",
                "tags": [
                    "Analytics"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/analytics\/properties": {
            "get": {
                "summary": "Get property analytics",
                "description": "Get property analytics",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/analytics\/users": {
            "get": {
                "summary": "Get user analytics",
                "description": "Get user analytics",
                "tags": [
                    "Users"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/reports\/sales": {
            "get": {
                "summary": "Get sales reports",
                "description": "Get sales reports",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/reports\/traffic": {
            "get": {
                "summary": "Get traffic reports",
                "description": "Get traffic reports",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/settings\/app": {
            "get": {
                "summary": "Get app settings",
                "description": "Get app settings",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "put": {
                "summary": "Update app settings",
                "description": "Update app settings",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/settings\/user": {
            "get": {
                "summary": "Get user settings",
                "description": "Get user settings",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "put": {
                "summary": "Update user settings",
                "description": "Update user settings",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/notifications": {
            "get": {
                "summary": "Get notifications",
                "description": "Get notifications",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Create notification",
                "description": "Create notification",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/notifications\/{id}\/read": {
            "put": {
                "summary": "Mark notification as read",
                "description": "Mark notification as read",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/messages": {
            "get": {
                "summary": "Get messages",
                "description": "Get messages",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            },
            "post": {
                "summary": "Send message",
                "description": "Send message",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/search\/properties": {
            "get": {
                "summary": "Search properties with filters",
                "description": "Search properties with filters",
                "tags": [
                    "Properties"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/search\/projects": {
            "get": {
                "summary": "Search projects",
                "description": "Search projects",
                "tags": [
                    "Projects"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/search\/users": {
            "get": {
                "summary": "Search users",
                "description": "Search users",
                "tags": [
                    "Users"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/filters\/property-types": {
            "get": {
                "summary": "Get property type filters",
                "description": "Get property type filters",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/filters\/locations": {
            "get": {
                "summary": "Get location filters",
                "description": "Get location filters",
                "tags": [
                    "General"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/ai\/chatbot": {
            "get": {
                "summary": "AI chatbot endpoint",
                "description": "AI chatbot endpoint",
                "tags": [
                    "AI"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/ai\/chatbot\/message": {
            "post": {
                "summary": "Send message to AI chatbot",
                "description": "Send message to AI chatbot",
                "tags": [
                    "AI"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/ai\/recommendations": {
            "get": {
                "summary": "Get AI recommendations",
                "description": "Get AI recommendations",
                "tags": [
                    "AI"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/ai\/analyze-property": {
            "post": {
                "summary": "AI property analysis",
                "description": "AI property analysis",
                "tags": [
                    "AI"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/system\/info": {
            "get": {
                "summary": "Get system information",
                "description": "Get system information",
                "tags": [
                    "System"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/system\/health": {
            "get": {
                "summary": "System health check",
                "description": "System health check",
                "tags": [
                    "System"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/system\/backup": {
            "post": {
                "summary": "Trigger system backup",
                "description": "Trigger system backup",
                "tags": [
                    "System"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        },
        "\/api\/system\/logs": {
            "get": {
                "summary": "Get system logs",
                "description": "Get system logs",
                "tags": [
                    "System"
                ],
                "responses": {
                    "200": {
                        "description": "Successful response",
                        "content": {
                            "application\/json": {
                                "schema": {
                                    "$ref": "#\/components\/schemas\/ApiResponse"
                                }
                            }
                        }
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Not found"
                    },
                    "500": {
                        "description": "Internal server error"
                    }
                }
            }
        }
    },
    "components": {
        "schemas": {
            "Property": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "title": {
                        "type": "string"
                    },
                    "description": {
                        "type": "string"
                    },
                    "price": {
                        "type": "number"
                    },
                    "location": {
                        "type": "string"
                    },
                    "status": {
                        "type": "string"
                    }
                }
            },
            "User": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string"
                    },
                    "email": {
                        "type": "string"
                    },
                    "role": {
                        "type": "string"
                    },
                    "created_at": {
                        "type": "string",
                        "format": "date-time"
                    }
                }
            },
            "ApiResponse": {
                "type": "object",
                "properties": {
                    "success": {
                        "type": "boolean"
                    },
                    "data": {
                        "type": "object"
                    },
                    "message": {
                        "type": "string"
                    },
                    "timestamp": {
                        "type": "string",
                        "format": "date-time"
                    }
                }
            }
        },
        "securitySchemes": {
            "bearerAuth": {
                "type": "http",
                "scheme": "bearer",
                "bearerFormat": "JWT"
            }
        }
    },
    "security": [
        {
            "bearerAuth": []
        }
    ],
    "tags": [
        {
            "name": "Authentication",
            "description": "User authentication endpoints"
        },
        {
            "name": "Properties",
            "description": "Property management endpoints"
        },
        {
            "name": "Projects",
            "description": "Project management endpoints"
        },
        {
            "name": "Users",
            "description": "User management endpoints"
        },
        {
            "name": "Analytics",
            "description": "Analytics and reporting endpoints"
        },
        {
            "name": "AI",
            "description": "AI-powered features endpoints"
        }
    ]
}