\---

title: "System Architecture"

type: architecture

\---



\# System Architecture



\## Tech Stack

| Layer       | Technology              | Purpose                     |

|-------------|-------------------------|-----------------------------|

| Frontend    | Next.js 15 + TypeScript | Main framework              |

| Styling     | Tailwind CSS            | UI styling                  |

| Backend     | NestJS or Next.js API   | API routes                  |

| Database    | PostgreSQL + Prisma     | Data storage                |

| Auth        | NextAuth / Clerk        | Authentication              |

| State       | Zustand or Redux        | Client state management     |



\## High-Level Architecture

\- \*\*Frontend\*\*: Next.js App Router

\- \*\*API Layer\*\*: Server Actions or API Routes

\- \*\*Database\*\*: Prisma ORM with PostgreSQL

\- \*\*Authentication\*\*: JWT-based or OAuth



\## Folder Structure (Frontend)



src/

├── app/                  # Next.js pages \& layouts

├── components/           # Reusable UI components

├── features/             # Feature-based components

├── lib/                  # Utilities \& helpers

├── hooks/                # Custom React hooks

└── types/                # TypeScript types

