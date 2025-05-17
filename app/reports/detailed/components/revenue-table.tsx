"use client"

import { useState } from "react"
import {
  type ColumnDef,
  type ColumnFiltersState,
  type SortingState,
  flexRender,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
} from "@tanstack/react-table"
import { ArrowUpDown, ChevronDown } from "lucide-react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { Skeleton } from "@/components/ui/skeleton"

interface RevenueData {
  id: string
  category: string
  website: string
  revenue: number
  orders: number
  percentage: number
  change: number
}

const data: RevenueData[] = [
  {
    id: "1",
    category: "Thời trang nam",
    website: "Shop Thời Trang",
    revenue: 35250000,
    orders: 78,
    percentage: 23.1,
    change: 12.5,
  },
  {
    id: "2",
    category: "Thời trang nữ",
    website: "Shop Thời Trang",
    revenue: 23180000,
    orders: 65,
    percentage: 15.2,
    change: 8.3,
  },
  {
    id: "3",
    category: "Điện thoại",
    website: "Shop Điện Tử",
    revenue: 42350000,
    orders: 32,
    percentage: 27.8,
    change: 15.2,
  },
  {
    id: "4",
    category: "Phụ kiện điện tử",
    website: "Shop Điện Tử",
    revenue: 23400000,
    orders: 45,
    percentage: 15.4,
    change: 10.5,
  },
  {
    id: "5",
    category: "Mỹ phẩm",
    website: "Shop Mỹ Phẩm",
    revenue: 28250000,
    orders: 85,
    percentage: 18.5,
    change: 10.8,
  },
  {
    id: "6",
    category: "Thời trang trẻ em",
    website: "Shop Thời Trang",
    revenue: 12500000,
    orders: 42,
    percentage: 8.2,
    change: 5.3,
  },
  {
    id: "7",
    category: "Laptop",
    website: "Shop Điện Tử",
    revenue: 35800000,
    orders: 18,
    percentage: 23.5,
    change: 18.7,
  },
  {
    id: "8",
    category: "Chăm sóc da",
    website: "Shop Mỹ Phẩm",
    revenue: 18500000,
    orders: 56,
    percentage: 12.1,
    change: 9.2,
  },
  {
    id: "9",
    category: "Trang sức",
    website: "Shop Thời Trang",
    revenue: 9800000,
    orders: 35,
    percentage: 6.4,
    change: 7.8,
  },
  {
    id: "10",
    category: "Máy tính bảng",
    website: "Shop Điện Tử",
    revenue: 15600000,
    orders: 12,
    percentage: 10.2,
    change: 12.3,
  },
]

const columns: ColumnDef<RevenueData>[] = [
  {
    accessorKey: "category",
    header: "Danh mục",
    cell: ({ row }) => <div className="font-medium">{row.getValue("category")}</div>,
  },
  {
    accessorKey: "website",
    header: "Website",
    cell: ({ row }) => <div>{row.getValue("website")}</div>,
  },
  {
    accessorKey: "revenue",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Doanh thu
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => {
      const amount = Number.parseFloat(row.getValue("revenue"))
      const formatted = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        maximumFractionDigits: 0,
      }).format(amount)

      return <div className="text-right font-medium">{formatted}</div>
    },
  },
  {
    accessorKey: "orders",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Số đơn hàng
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => <div className="text-right">{row.getValue("orders")}</div>,
  },
  {
    accessorKey: "percentage",
    header: "Tỷ lệ",
    cell: ({ row }) => <div className="text-right">{row.getValue("percentage")}%</div>,
  },
  {
    accessorKey: "change",
    header: "So với kỳ trước",
    cell: ({ row }) => {
      const change = Number.parseFloat(row.getValue("change"))
      return (
        <div className={`text-right ${change > 0 ? "text-green-500" : "text-red-500"}`}>
          {change > 0 ? "+" : ""}
          {change}%
        </div>
      )
    },
  },
]

interface RevenueTableProps {
  isLoading?: boolean
}

export function RevenueTable({ isLoading = false }: RevenueTableProps) {
  const [sorting, setSorting] = useState<SortingState>([])
  const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([])
  const [columnVisibility, setColumnVisibility] = useState({})

  const table = useReactTable({
    data,
    columns,
    onSortingChange: setSorting,
    onColumnFiltersChange: setColumnFilters,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onColumnVisibilityChange: setColumnVisibility,
    state: {
      sorting,
      columnFilters,
      columnVisibility,
    },
  })

  if (isLoading) {
    return (
      <div className="w-full">
        <div className="flex items-center py-4">
          <Skeleton className="h-10 w-[250px]" />
          <Skeleton className="h-10 w-[110px] ml-auto" />
        </div>
        <div className="rounded-md border">
          <Skeleton className="h-[400px] w-full" />
        </div>
        <div className="flex items-center justify-end space-x-2 py-4">
          <Skeleton className="h-10 w-[100px]" />
          <Skeleton className="h-10 w-[70px]" />
        </div>
      </div>
    )
  }

  return (
    <div className="w-full">
      <div className="flex items-center py-4">
        <Input
          placeholder="Tìm kiếm theo danh mục..."
          value={(table.getColumn("category")?.getFilterValue() as string) ?? ""}
          onChange={(event) => table.getColumn("category")?.setFilterValue(event.target.value)}
          className="max-w-sm"
        />
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <Button variant="outline" className="ml-auto">
              Cột <ChevronDown className="ml-2 h-4 w-4" />
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end">
            {table
              .getAllColumns()
              .filter((column) => column.getCanHide())
              .map((column) => {
                return (
                  <DropdownMenuCheckboxItem
                    key={column.id}
                    className="capitalize"
                    checked={column.getIsVisible()}
                    onCheckedChange={(value) => column.toggleVisibility(!!value)}
                  >
                    {column.id === "category"
                      ? "Danh mục"
                      : column.id === "website"
                        ? "Website"
                        : column.id === "revenue"
                          ? "Doanh thu"
                          : column.id === "orders"
                            ? "Số đơn hàng"
                            : column.id === "percentage"
                              ? "Tỷ lệ"
                              : column.id === "change"
                                ? "So với kỳ trước"
                                : column.id}
                  </DropdownMenuCheckboxItem>
                )
              })}
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
      <div className="rounded-md border">
        <Table>
          <TableHeader>
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header) => {
                  return (
                    <TableHead key={header.id}>
                      {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                    </TableHead>
                  )
                })}
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows?.length ? (
              table.getRowModel().rows.map((row) => (
                <TableRow key={row.id} data-state={row.getIsSelected() && "selected"}>
                  {row.getVisibleCells().map((cell) => (
                    <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                  ))}
                </TableRow>
              ))
            ) : (
              <TableRow>
                <TableCell colSpan={columns.length} className="h-24 text-center">
                  Không tìm thấy kết quả.
                </TableCell>
              </TableRow>
            )}
          </TableBody>
        </Table>
      </div>
      <div className="flex items-center justify-end space-x-2 py-4">
        <div className="flex-1 text-sm text-muted-foreground">
          Hiển thị {table.getRowModel().rows.length} trong tổng số {data.length} dòng.
        </div>
        <div className="space-x-2">
          <Button
            variant="outline"
            size="sm"
            onClick={() => table.previousPage()}
            disabled={!table.getCanPreviousPage()}
          >
            Trước
          </Button>
          <Button variant="outline" size="sm" onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}>
            Sau
          </Button>
        </div>
      </div>
    </div>
  )
}
