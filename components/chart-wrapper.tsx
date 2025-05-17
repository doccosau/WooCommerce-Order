import type React from "react"

interface ChartWrapperProps {
  children: React.ReactNode
  className?: string
  title?: string
}

export function ChartWrapper({ children, className, title }: ChartWrapperProps) {
  return (
    <div className={className}>
      {title && <span className="sr-only">{title}</span>}
      {children}
    </div>
  )
}
