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

## Screenshots

### Tenant Overview

![Tenant overview](docs/images/tenants.png)

### Alert Webhooks

![Alert webhooks](docs/images/alerts.png)

### Halo Billing Integration

![Halo billing integration](docs/images/centralbilling.png)

### Firewall Upgrade Planning

<img width="875" height="702" alt="Firewall upgrade planning" src="https://github.com/user-attachments/assets/7ad1a3db-c08a-4fa6-b703-36a59678f605" />

### Health and Design Preview

<img width="1403" height="671" alt="Design preview" src="https://github.com/user-attachments/assets/37ba2c88-98d7-42b6-84f4-2bd43a0b04ca" />
<img width="1125" height="792" alt="Tenant overview and search" src="https://github.com/user-attachments/assets/2b2f501d-73d5-44bd-b4c9-649617710672" />
<img width="1393" height="927" alt="Alerts view" src="https://github.com/user-attachments/assets/639c0e4e-c3eb-4f69-9e23-d46923637792" />
<img width="1110" height="419" alt="Endpoints view" src="https://github.com/user-attachments/assets/0c34d718-b9f9-4398-90ce-431dd62d6d71" />
<img width="1192" height="574" alt="Firewalls view" src="https://github.com/user-attachments/assets/be6eb9cf-f1a5-4ef9-8b45-2da3cf4def97" />
<img width="1200" height="630" alt="Overall tenant health reporting" src="https://github.com/user-attachments/assets/6a2b5031-aa49-400c-829d-feb56b28199b" />

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

