# ISCPP

ISCPP (Improve Sophos Central Partner Package) operations platform for MSPs that use Sophos Central.

It helps teams manage multiple tenants from one place, automate recurring operational tasks, and integrate Sophos data into PSA workflows.

## Why ISCPP

Sophos Central is powerful, but MSP workflows often require cross-tenant visibility and automation that are not available out of the box.

ISCPP focuses on those gaps by combining:

- Cross-tenant monitoring and search
- Alert forwarding and webhook workflows
- Billing-related data exports and integrations
- Firewall upgrade planning support
- Centralized health score reporting

More background: [Project Wiki](https://github.com/xasz/ISCPP/wiki)

## Core Features

- **Multi-tenant overview** dashboard
- **Search endpoints** across all Sophos Central tenants
- Sophos Central **alert forwarding** via webhooks
- **Halo PSA** billing integration
- **NinjaOne** endpoint deployment support
- F**irewall** upgrade planning
- Tenant **health score** reporting

## Screenshots and Impressions

### Tenant

<img width="1415" height="1045" alt="image" src="https://github.com/user-attachments/assets/06817d14-14b3-43b6-ba2c-034931478041" />

### Alerts

<img width="1418" height="991" alt="image" src="https://github.com/user-attachments/assets/c7e59fb6-aa8e-493f-abfb-e521c2b1a8cf" />

### Healthscores

<img width="1411" height="748" alt="image" src="https://github.com/user-attachments/assets/470d693c-1407-4472-a6cc-d6d5705ea7a3" />

### Firewall Upgrade Schedule

<img width="1435" height="759" alt="image" src="https://github.com/user-attachments/assets/ef4b53b6-5761-4d8b-b787-4ac0dad86788" />

### Webhook Forwarding

<img width="1407" height="937" alt="image" src="https://github.com/user-attachments/assets/f6fbbd2c-cebf-4126-9925-7f7ae48b23b7" />

# Fetch and Push Billing Data From Sophos Central

<img width="1428" height="282" alt="image" src="https://github.com/user-attachments/assets/f1ec41d3-3fca-4524-9385-880e4c17b05c" />


## Quick Start

```bash
git clone https://github.com/xasz/iscpp-docker
docker `compose up -d'
```

## Installation Guide

Detailed installation steps are available in the wiki:
[Installation Guide](https://github.com/xasz/ISCPP/wiki/1.-Installation)

## Project Status

ISCPP is currently in alpha. It is already used in early live environments, and active feedback is shaping upcoming releases.

## Support

- Discord: https://discord.gg/GqTMtdMY
- Issues: https://github.com/xasz/ISCPP/issues

## Disclaimer

This project is provided as-is. While care is taken to build secure and reliable functionality, you are responsible for validating suitability, security, and compliance in your own environment.

