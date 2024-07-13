import React from 'react'
import ReactDOM from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { ReactQueryDevtools } from '@tanstack/react-query-devtools'
import { app1Selector } from '@/utils'
import { StyleProvider } from '@ant-design/cssinjs'

const App1 = React.lazy(() => import('./App1'))

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      refetchOnWindowFocus: false,
      retry: 0,
    },
  },
})

const app1Nodes = document.querySelectorAll(app1Selector)

const mapping = [
  {
    els: app1Nodes,
    App: App1,
  },
]

mapping.forEach(({ els, App }) => {
  if (!!els) {
    els.forEach((el) => {
      ReactDOM.createRoot(el).render(
        <React.StrictMode>
          <QueryClientProvider client={queryClient}>
            <StyleProvider hashPriority="high">
              <App />
            </StyleProvider>
            <ReactQueryDevtools initialIsOpen={false} />
          </QueryClientProvider>
        </React.StrictMode>,
      )
    })
  }
})
