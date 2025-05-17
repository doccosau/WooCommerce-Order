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
import { Badge } from "@/components/ui/badge"

interface OrdersData {
  id: string
  order_number: string
  website: string
  date: string
  customer: string
  status: string
  payment_method: string
  total: number
}

const data: OrdersData[] = [
  {
    id: "1",
    order_number: "#WC-12345",
    website: "Shop Thời Trang",
    date: "15/05/2023",
    customer: "Nguyễn Văn A",
    status: "completed",
    payment_method: "COD",
    total: 1250000,
  },
  {
    id: "2",
    order_number: "#WC-12346",
    website: "Shop Điện Tử",
    date: "15/05/2023",
    customer: "Trần Thị B",
    status: "processing",
    payment_method: "Bank Transfer",
    total: 3500000,
  },
  {
    id: "3",
    order_number: "#WC-12347",
    website: "Shop Mỹ Phẩm",
    date: "14/05/2023",
    customer: "Lê Văn C",
    status: "completed",
    payment_method: "Credit Card",
    total: 850000,
  },
  {
    id: "4",
    order_number: "#WC-12348",
    website: "Shop Thời Trang",
    date: "14/05/2023",
    customer: "Phạm Thị D",
    status: "completed",
    payment_method: "COD",
    total: 1450000,
  },
  {
    id: "5",
    order_number: "#WC-12349",
    website: "Shop Điện Tử",
    date: "13/05/2023",
    customer: "Hoàng Văn E",
    status: "cancelled",
    payment_method: "Bank Transfer",
    total: 2800000,
  },
  {
    id: "6",
    order_number: "#WC-12350",
    website: "Shop Mỹ Phẩm",
    date: "13/05/2023",
    customer: "Ngô Thị F",
    status: "completed",
    payment_method: "E-wallet",
    total: 750000,
  },
  {
    id: "7",
    order_number: "#WC-12351",
    website: "Shop Thời Trang",
    date: "12/05/2023",
    customer: "Đặng Văn G",
    status: "processing",
    payment_method: "COD",
    total: 1650000,
  },
  {
    id: "8",
    order_number: "#WC-12352",
    website: "Shop Điện Tử",
    date: "12/05/2023",
    customer: "Vũ Thị H",
    status: "completed",
    payment_method: "Credit Card",
    total: 4200000,
  },
  {
    id: "9",
    order_number: "#WC-12353",
    website: "Shop Mỹ Phẩm",
    date: "11/05/2023",
    customer: "Bùi Văn I",
    status: "completed",
    payment_method: "Bank Transfer",
    total: 950000,
  },
  {
    id: "10",
    order_number: "#WC-12354",
    website: "Shop Thời Trang",
    date: "11/05/2023",
    customer: "Lý Thị K",
    status: "processing",
    payment_method: "COD",
    total: 1350000,
  },
]

const columns: ColumnDef<OrdersData>[] = [
  {
    accessorKey: "order_number",
    header: "Mã đơn hàng",
    cell: ({ row }) => <div className="font-medium">{row.getValue("order_number")}</div>,
  },
  {
    accessorKey: "website",
    header: "Website",
    cell: ({ row }) => <div>{row.getValue("website")}</div>,
  },
  {
    accessorKey: "date",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Ngày đặt
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => <div>{row.getValue("date")}</div>,
  },
  {
    accessorKey: "customer",
    header: "Khách hàng",
    cell: ({ row }) => <div>{row.getValue("customer")}</div>,
  },
  {
    accessorKey: "status",
    header: "Trạng thái",
    cell: ({ row }) => {
      const status = row.getValue("status") as string
      return (
        <Badge
          className={
            status === "completed"
              ? "bg-green-500"
              : status === "processing"
                ? "bg-blue-500"
                : status === "cancelled"
                  ? "bg-red-500"
                  : ""
          }
        >
          {status === "completed"
            ? "Hoàn thành"
            : status === "processing"
              ? "Đang xử lý"
              : status === "cancelled"
                ? "Đã hủy"
                : status}
        </Badge>
      )
    },
  },
  {
    accessorKey: "payment_method",
    header: "Phương thức thanh toán",
    cell: ({ row }) => <div>{row.getValue("payment_method")}</div>,
  },
  {
    accessorKey: "total",
    header: ({ column }) => {
      return (
        <Button variant="ghost" onClick={() => column.toggleSorting(column.getIsSorted() === "asc")}>
          Tổng tiền
          <ArrowUpDown className="ml-2 h-4 w-4" />
        </Button>
      )
    },
    cell: ({ row }) => {
      const amount = Number.parseFloat(row.getValue("total"))
      const formatted = new Intl.NumberFormat("vi-VN", {
        style: "currency",
        currency: "VND",
        maximumFractionDigits: 0,
      }).format(amount)

      return <div className="text-right font-medium">{formatted}</div>
    },
  },
]

interface OrdersTableProps {
  isLoading?: boolean
}

export function OrdersTable({ isLoading = false }: OrdersTableProps) {
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
          placeholder="Tìm kiếm theo mã đơn hàng..."
          value={(table.getColumn("order_number")?.getFilterValue() as string) ?? ""}
          onChange={(event) => table.getColumn("order_number")?.setFilterValue(event.target.value)}
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
                    {column.id === "order_number"
                      ? "Mã đơn hàng"
                      : column.id === "website"
                        ? "Website"
                        : column.id === "date"
                          ? "Ngày đặt"
                          : column.id === "customer"
                            ? "Khách hàng"
                            : column.id === "status"
                              ? "Trạng thái"
                              : column.id === "payment_method"
                                ? "Phương thức thanh toán"
                                : column.id === "total"
                                  ? "Tổng tiền"
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
